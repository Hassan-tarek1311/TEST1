<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        // لو الموظف موقوف، منعهوش من الدخول حتى لو عنده token صحيح
        if (auth()->check() && !auth()->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact your admin.',
            ], 403);
        }

        return $next($request);
    }
}
