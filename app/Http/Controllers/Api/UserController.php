<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ===========================
    // GET /api/users
    // المدير يشوف كل موظفي شركته
    // ===========================
    public function index(): JsonResponse
    {
        $this->requireAdmin();

        $users = User::where('organization_id', auth()->user()->organization_id)
            ->withCount('timeLogs')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    // ===========================
    // POST /api/users
    // المدير يضيف موظف جديد للشركة
    // ===========================
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'in:admin,employee',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'organization_id' => auth()->user()->organization_id,
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => $request->role ?? 'employee',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee added successfully!',
            'data'    => $user,
        ], 201);
    }

    // ===========================
    // GET /api/users/{id}
    // ===========================
    public function show(int $id): JsonResponse
    {
        $this->requireAdmin();

        $user = User::where('organization_id', auth()->user()->organization_id)
            ->with('timeLogs.project')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    // ===========================
    // PUT /api/users/{id}
    // ===========================
    public function update(Request $request, int $id): JsonResponse
    {
        $this->requireAdmin();

        $user = User::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role'  => 'sometimes|in:admin,employee',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'success' => true,
            'message' => 'User updated.',
            'data'    => $user,
        ]);
    }

    // ===========================
    // PATCH /api/users/{id}/toggle-status
    // تفعيل أو إيقاف موظف
    // ===========================
    public function toggleStatus(int $id): JsonResponse
    {
        $this->requireAdmin();

        $user = User::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        // مينفعش المدير يوقف نفسه
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully.",
            'data'    => $user,
        ]);
    }

    // ===========================
    // Helper: تأكد إن المستخدم الحالي admin
    // ===========================
    private function requireAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Admin access required.',
            ], 403));
        }
    }
}
