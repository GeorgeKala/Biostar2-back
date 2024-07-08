<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $validation = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required'
    ]);

    if ($validation->fails()) {
        return response()->json($validation->errors(), 400);
    }

    $user = User::where('username', $request->username)->first();

    if ($user) {
        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'User' => [
                    'login_id' => 'admin',
                    'password' => 'admin256'
                ]
            ];

            $biostarUrl = 'https://10.150.20.173/api/login';

            try {
                $response = Http::withOptions(['verify' => false])
                    ->post($biostarUrl, $userData);
                
                if ($response->successful()) { 
                    $bsSessionId = $response->headers()['bs-session-id'][0];
                
                    return response()->json([
                        'token' => $token,
                        'user' => $user,
                        'bs-session-id' => $bsSessionId,
                        'status' => 200
                    ]);
                } else {
                    return response()->json([
                        'error' => 'Unexpected response from Biostar API',
                        'response' => $response
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Password is incorrect'
            ]);
        }
    } else {
        return response()->json([
            'status' => 404,
            'message' => 'Username not found'
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
