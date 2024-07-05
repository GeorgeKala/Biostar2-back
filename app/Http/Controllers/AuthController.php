<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status' => 200,
                    'message' => 'Login Success',
                    'token' => $token,
                    'user' => $user
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Password is incorrect'
                ]);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Email not found'
            ]);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Logout successfully'
        ]);
    }

    
}
