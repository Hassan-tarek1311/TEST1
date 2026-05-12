<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class WebAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = session('jwt_token');

        if (!$token) {
            return redirect()->route('login');
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                session()->forget('jwt_token');
                return redirect()->route('login');
            }
            // Set the authenticated user
            auth()->setUser($user);
        } catch (JWTException $e) {
            session()->forget('jwt_token');
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        return $next($request);
    }
}
