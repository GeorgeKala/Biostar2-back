<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validation->errors(),
            ], 400);
        }

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = [
            'User' => [
                'login_id' => env('BIOSTAR_ADMIN_USER'),
                'password' => env('BIOSTAR_ADMIN_PASSWORD'),
            ],
        ];

        $biostarUrl = 'https://10.150.20.173/api/login';

        try {
            $response = Http::withOptions(['verify' => false])
                ->post($biostarUrl, $userData);

            if ($response->successful()) {
                $bsSessionId = $response->header('bs-session-id');

                return response()->json([
                    'status' => 200,
                    'token' => $token,
                    'user' => $user,
                    'bs-session-id' => $bsSessionId,
                ]);
            } else {
                return response()->json([
                    'status' => $response->status(),
                    'error' => 'Unexpected response from Biostar API',
                    'response' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An error occurred while trying to connect to the Biostar API.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Logout successfully',
        ]);
    }
}
