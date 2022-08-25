<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\{ Request, Response };
use Illuminate\Support\Facades\{ Validator, Auth };
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'The credentials provided are not found'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $user = User::firstWhere('email', $request->email);
        $token = $user->createToken('auth-token')->plainTextToken; 

        return response()->json([
            'message' => 'Login successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successfully'
        ], Response::HTTP_OK);
    }
}
