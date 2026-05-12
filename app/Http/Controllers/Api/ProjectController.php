<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // GET /api/projects
    public function index(): JsonResponse
    {
        $projects = Project::where('organization_id', auth()->user()->organization_id)
            ->withCount('timeLogs')
            ->withSum('timeLogs', 'duration_minutes')
            ->get()
            ->map(function ($project) {
                $mins = (int) ($project->time_logs_sum_duration_minutes ?? 0);
                $project->total_hours = round($mins / 60, 2);

                return $project;
            });

        return response()->json([
            'success' => true,
            'data'    => $projects,
        ]);
    }

    // POST /api/projects
    public function store(Request $request): JsonResponse
    {
        // بس الـ admin يقدر يضيف مشاريع
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can create projects.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'in:active,completed,paused',
            'deadline'    => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $project = Project::create([
            'organization_id' => auth()->user()->organization_id,
            'name'            => $request->name,
            'description'     => $request->description,
            'status'          => $request->status ?? 'active',
            'deadline'        => $request->deadline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully!',
            'data'    => $project,
        ], 201);
    }

    // GET /api/projects/{id}
    public function show(int $id): JsonResponse
    {
        $project = Project::where('organization_id', auth()->user()->organization_id)
            ->with('timeLogs.user')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $project,
        ]);
    }

    // PUT /api/projects/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = Project::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $project->update($request->only(['name', 'description', 'status', 'deadline']));

        return response()->json([
            'success' => true,
            'message' => 'Project updated.',
            'data'    => $project,
        ]);
    }

    // DELETE /api/projects/{id}
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = Project::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted.',
        ]);
    }
}
