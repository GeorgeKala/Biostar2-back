<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest; 

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('username', 'password');
        
        if (Auth::attempt($credentials)) {
            return response()->json(['message' => 'Successfully logged in']);
        }

        $request->session()->regenerate();
        return response()->json(['message' => 'Invalid Credentials'], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => true, 'message' => 'User logged out successfully'], 200);
    }
}
