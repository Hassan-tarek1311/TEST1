<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // ===========================
    // POST /api/auth/register
    // تسجيل شركة جديدة مع أول مدير
    // ===========================
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'org_name'  => 'required|string|max:255',
            'org_email' => 'required|email|unique:organizations,email',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 1. إنشاء الشركة
        $organization = Organization::create([
            'name'  => $request->org_name,
            'email' => $request->org_email,
        ]);

        // 2. إنشاء المدير (admin)
        $user = User::create([
            'organization_id' => $organization->id,
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => 'admin',
        ]);

        // 3. إنشاء JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful!',
            'data' => [
                'user'         => $user,
                'organization' => $organization,
                'token'        => $token,
                'token_type'   => 'bearer',
            ],
        ], 201);
    }

    // ===========================
    // POST /api/auth/login
    // ===========================
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // JWTAuth::attempt بترجع token لو الكريدنشيالز صح
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = auth()->user()->load('organization');

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'data' => [
                'user'       => $user,
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // بالثواني
            ],
        ]);
    }

    // ===========================
    // POST /api/auth/logout
    // ===========================
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // ===========================
    // GET /api/auth/me
    // بيانات المستخدم الحالي
    // ===========================
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => auth()->user()->load('organization'),
        ]);
    }
}
