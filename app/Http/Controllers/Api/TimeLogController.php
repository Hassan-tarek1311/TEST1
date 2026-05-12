<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TimeLogController extends Controller
{
    // ===========================
    // GET /api/time-logs
    // عرض كل سجلات الوقت للمستخدم الحالي
    // ===========================
    public function index(Request $request): JsonResponse
    {
        $query = TimeLog::with(['project', 'user'])
            ->where('user_id', auth()->id());

        // فلترة بيوم واحد (اختياري)
        if ($request->filled('date')) {
            $query->whereDate('started_at', $request->date);
        }

        // فلترة بنطاق تواريخ (اختياري — للوحة التحكم: أسبوع، إلخ)
        if ($request->filled('from')) {
            $query->whereDate('started_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('started_at', '<=', $request->to);
        }

        // فلترة بالمشروع (اختياري)
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // سجلات لم تُغلق بعد (مؤقت نشط)
        if ($request->boolean('active_only')) {
            $query->whereNull('ended_at');
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $logs = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }

    // ===========================
    // POST /api/time-logs/start
    // بداية تسجيل الوقت
    // ===========================
    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'notes'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // تأكد إن الموظف مش شغّال حاجة تانية دلوقتي
        $activeLog = TimeLog::where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        if ($activeLog) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active time log. Stop it first!',
                'data'    => $activeLog,
            ], 409);
        }

        $timeLog = TimeLog::create([
            'user_id'    => auth()->id(),
            'project_id' => $request->project_id,
            'started_at' => Carbon::now(),
            'notes'      => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time tracking started!',
            'data'    => $timeLog->load('project'),
        ], 201);
    }

    // ===========================
    // PATCH /api/time-logs/{id}/stop
    // إيقاف تسجيل الوقت
    // ===========================
    public function stop(int $id): JsonResponse
    {
        $timeLog = TimeLog::where('id', $id)
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'No active time log found with this ID.',
            ], 404);
        }

        $timeLog->stop(); // الميثود اللي كتبناها في الـ Model

        return response()->json([
            'success' => true,
            'message' => 'Time tracking stopped!',
            'data'    => [
                'time_log'  => $timeLog->load('project'),
                'duration'  => $timeLog->duration_formatted,
            ],
        ]);
    }

    // ===========================
    // GET /api/time-logs/{id}
    // ===========================
    public function show(int $id): JsonResponse
    {
        $timeLog = TimeLog::with(['project', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $timeLog,
        ]);
    }

    // ===========================
    // DELETE /api/time-logs/{id}
    // ===========================
    public function destroy(int $id): JsonResponse
    {
        $timeLog = TimeLog::where('user_id', auth()->id())
            ->findOrFail($id);

        $timeLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time log deleted.',
        ]);
    }
}
