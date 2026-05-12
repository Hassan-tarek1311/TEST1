<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        if (session('jwt_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return back()->with('error', 'Invalid email or password.')->withInput();
        }

        session(['jwt_token' => $token]);
        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_name'  => 'required|string|max:255',
            'org_email' => 'required|email|unique:organizations,email',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $org = Organization::create([
            'name'  => $request->org_name,
            'email' => $request->org_email,
        ]);

        $user = User::create([
            'organization_id' => $org->id,
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => 'admin',
        ]);

        $token = JWTAuth::fromUser($user);
        session(['jwt_token' => $token]);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        try {
            if ($token = session('jwt_token')) {
                JWTAuth::setToken($token)->invalidate();
            }
        } catch (\Exception $e) {}

        session()->forget('jwt_token');
        return redirect()->route('login');
    }
}
