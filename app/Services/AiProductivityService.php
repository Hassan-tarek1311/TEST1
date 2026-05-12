<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AiProductivityService
{
    // ===========================
    // الدالة الرئيسية
    // بتحلل بيانات الموظف وترجع تقرير
    // ===========================
    public function analyzeProductivity(User $user, int $days = 7): array
    {
        // 1. جلب بيانات الموظف
        $stats = $this->collectStats($user, $days);

        // 2. إرسال للـ AI (أو mock)
        $provider = config('app.ai_provider', 'mock');
        
        if ($provider === 'openai') {
            return $this->analyzeWithOpenAI($stats);
        } elseif ($provider === 'gemini') {
            return $this->analyzeWithGemini($stats);
        }

        return $this->analyzeWithMock($stats);
    }

    // ===========================
    // جمع إحصائيات الموظف
    // ===========================
    private function collectStats(User $user, int $days): array
    {
        $from = Carbon::now()->subDays($days);

        $logs = TimeLog::where('user_id', $user->id)
            ->where('started_at', '>=', $from)
            ->whereNotNull('ended_at')
            ->with('project')
            ->get();

        $totalMinutes   = $logs->sum('duration_minutes');
        $totalSessions  = $logs->count();
        $avgPerDay      = $days > 0 ? round($totalMinutes / $days) : 0;

        // تحليل بالمشروع
        $byProject = $logs->groupBy('project.name')
            ->map(fn($group) => [
                'sessions' => $group->count(),
                'minutes'  => $group->sum('duration_minutes'),
            ]);

        // أكثر يوم شغل فيه
        $byDay = $logs->groupBy(fn($log) => $log->started_at->format('l'))
            ->map(fn($group) => $group->sum('duration_minutes'))
            ->sortDesc();

        return [
            'user_name'      => $user->name,
            'period_days'    => $days,
            'total_hours'    => round($totalMinutes / 60, 1),
            'total_sessions' => $totalSessions,
            'avg_hours_day'  => round($avgPerDay / 60, 1),
            'by_project'     => $byProject->toArray(),
            'busiest_day'    => $byDay->keys()->first() ?? 'N/A',
        ];
    }

    // ===========================
    // تحليل بـ OpenAI
    // ===========================
    private function analyzeWithOpenAI(array $stats): array
    {
        $client = new \GuzzleHttp\Client();

        $prompt = $this->buildPrompt($stats);

        try {
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'You are an HR productivity analyst. Analyze employee time tracking data and provide constructive feedback in JSON format.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'response_format' => ['type' => 'json_object'],
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            $content = json_decode($result['choices'][0]['message']['content'], true);

            // Map to frontend expected structure
            $status = $content['status'] ?? 'N/A';
            $tip = $content['tip'] ?? 'N/A';
            $summary = $content['summary'] ?? ($stats['busiest_day'] ?? 'No summary available');

            return array_merge($stats, [
                'productivity_score' => $content['productivity_score'] ?? 50,
                'total_logs' => $stats['total_sessions'] ?? 0,
                'insights' => [
                    "Status: {$status}",
                    "Tip: {$tip}"
                ],
                'summary' => $summary,
                'message' => $content['status'] ?? 'Analysis complete',
                'recommendation' => $content['tip'] ?? 'Keep up the good work!',
                'provider' => 'openai',
            ]);
        } catch (\Exception $e) {
            // Fallback to mock if OpenAI fails
            return $this->analyzeWithMock($stats);
        }
    }

    // ===========================
    // تحليل بـ Gemini
    // ===========================
    private function analyzeWithGemini(array $stats): array
    {
        $client = new \GuzzleHttp\Client();

        $prompt = $this->buildPrompt($stats);

        try {
            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . env('GEMINI_API_KEY'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'You are an HR productivity analyst. Analyze employee time tracking data and provide constructive feedback in JSON format with keys: productivity_score (0-100), status (Excellent/Good/Average/Needs Improvement), tip (actionable advice), summary. Data: ' . json_encode($stats),
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid Gemini API response');
            }
            
            $content = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to parse Gemini response: ' . json_last_error_msg());
            }

            // Map to frontend expected structure
            $status = $content['status'] ?? 'N/A';
            $tip = $content['tip'] ?? 'N/A';
            $summary = $content['summary'] ?? ($stats['busiest_day'] ?? 'No summary available');
            
            return array_merge($stats, [
                'productivity_score' => $content['productivity_score'] ?? 50,
                'total_logs' => $stats['total_sessions'] ?? 0,
                'insights' => [
                    "Status: {$status}",
                    "Tip: {$tip}"
                ],
                'summary' => $summary,
                'message' => $content['status'] ?? 'Analysis complete',
                'recommendation' => $content['tip'] ?? 'Keep up the good work!',
                'provider' => 'gemini',
            ]);
        } catch (\Exception $e) {
            // Fallback to mock if Gemini fails
            return $this->analyzeWithMock($stats);
        }
    }

    // ===========================
    // Mock AI (للتطوير بدون OpenAI)
    // ===========================
    private function analyzeWithMock(array $stats): array
    {
        $hours = $stats['total_hours'];
        $avg   = $stats['avg_hours_day'];

        // منطق بسيط لتقييم الإنتاجية
        if ($avg >= 7) {
            $score  = 95;
            $status = 'Excellent';
            $tip    = 'Outstanding performance! Make sure to take regular breaks to avoid burnout.';
        } elseif ($avg >= 5) {
            $score  = 75;
            $status = 'Good';
            $tip    = 'Good productivity level. Consider focusing on one project at a time for deeper work.';
        } elseif ($avg >= 3) {
            $score  = 50;
            $status = 'Average';
            $tip    = 'There is room for improvement. Try time-blocking techniques to stay focused.';
        } else {
            $score  = 25;
            $status = 'Needs Improvement';
            $tip    = 'Low tracked hours detected. Check if there are any blockers or if work is being done offline.';
        }

        $summary = "In the last {$stats['period_days']} days, {$stats['user_name']} logged {$hours} hours across {$stats['total_sessions']} sessions. Most active day: {$stats['busiest_day']}.";

        return array_merge($stats, [
            'productivity_score' => $score,
            'total_logs' => $stats['total_sessions'] ?? 0,
            'insights' => [
                "Status: {$status}",
                "Tip: {$tip}"
            ],
            'summary' => $summary,
            'message' => $status,
            'recommendation' => $tip,
            'provider' => 'mock',
        ]);
    }

    private function buildPrompt(array $stats): string
    {
        return "Analyze this employee's productivity data and return JSON with keys: 
        productivity_score (0-100), status (Excellent/Good/Average/Needs Improvement), tip (actionable advice), summary.
        
        Data: " . json_encode($stats);
    }
}
