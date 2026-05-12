<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AiProductivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        private AiProductivityService $aiService
    ) {}

    // ===========================
    // GET /api/ai/productivity/{userId}
    // تقرير إنتاجية الموظف بالذكاء الاصطناعي
    // ===========================
    public function productivityReport(Request $request, int $userId): JsonResponse
    {
        // المدير بس يقدر يشوف تقرير أي موظف
        // الموظف يقدر يشوف تقرير نفسه بس
        $currentUser = auth()->user();

        if (!$currentUser->isAdmin() && $currentUser->id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own productivity report.',
            ], 403);
        }

        $user = User::where('organization_id', $currentUser->organization_id)
            ->findOrFail($userId);

        $days = $request->get('days', 7); // آخر 7 أيام افتراضياً

        try {
            $report = $this->aiService->analyzeProductivity($user, $days);

            return response()->json([
                'success' => true,
                'message' => 'Productivity report generated!',
                'data'    => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI analysis failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ===========================
    // GET /api/ai/team-summary
    // ملخص إنتاجية الفريق كله (للأدمن)
    // ===========================
    public function teamSummary(Request $request): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required.',
            ], 403);
        }

        $days  = $request->get('days', 7);
        $users = User::where('organization_id', auth()->user()->organization_id)
            ->get();

        $reports = $users->map(function ($user) use ($days) {
            return $this->aiService->analyzeProductivity($user, $days);
        });

        // Calculate team metrics
        $totalScore = $reports->sum('productivity_score', 0);
        $teamSize = $users->count();
        $teamProductivityScore = $teamSize > 0 ? round($totalScore / $teamSize) : 0;

        // Find top performer
        $topPerformer = $reports->sortByDesc('productivity_score')->first();
        $topPerformerName = $topPerformer['user_name'] ?? 'N/A';

        // Generate team insights
        $teamInsights = [
            "Team size: {$teamSize} members",
            "Average productivity score: {$teamProductivityScore}/100",
            "Top performer: {$topPerformerName}"
        ];

        // Generate summary
        $totalHours = $reports->sum('total_hours', 0);
        $summary = "In the last {$days} days, the team logged {$totalHours} hours across {$teamSize} members with an average productivity score of {$teamProductivityScore}.";

        return response()->json([
            'success' => true,
            'data' => [
                'team_productivity_score' => $teamProductivityScore,
                'summary' => $summary,
                'team_insights' => $teamInsights,
                'top_performer' => $topPerformerName,
                'period_days' => $days,
                'team_size' => $teamSize,
                'reports' => $reports,
            ],
        ]);
    }
}
