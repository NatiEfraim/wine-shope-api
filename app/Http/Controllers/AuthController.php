<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    //
    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
    try {
       $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'personal_id' => ['required', 'string', 'unique:users,personal_id'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $tokenName = config('auth.token_name', 'access_token');
        $token = $user->createToken($tokenName);

        return response()
            ->json([
                'message' => 'User registered successfully',
                'name' => $user->name,
                'personal_id' => $user->personal_id,
                'token_type' => 'Bearer',
            ], Response::HTTP_CREATED)
            ->withCookie(
                Cookie::make(
                    $tokenName,
                    $token->accessToken,
                    60 * 24
                )
            );

        } catch (\Throwable $e) {
            Log::error('Register error: ' . $e->getMessage());

            return response()->json([
          
                'message' => 'Registration failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * POST /api/auth/login
     */
    public function login(Request $request)
    {

 try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                  
                    'message' => 'Invalid credentials',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $tokenName = config('auth.token_name', 'access_token');
            $token = $user->createToken($tokenName);

            return response()
                ->json([
                    'message' => 'User logged in successfully',
                    'name' => $user->name,
                    'personal_id' => $user->personal_id,
                    'token_type' => 'Bearer',
                ], Response::HTTP_OK)
                ->withCookie(
                    Cookie::make(
                        $tokenName,
                        $token->accessToken,
                        60 * 24, 
                    )
                );

        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

        /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
           try {
            if (!$request->user()) {
                return response()->json([
                
                    'message' => 'Unauthorized',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $request->user()->token()->revoke();

            $tokenName = config('auth.token_name', 'access_token');

            return response()->json([
                'message' => 'User logged out successfully',
            ], Response::HTTP_OK)
            ->withCookie(
                Cookie::forget($tokenName)
            );

        } catch (\Throwable $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Logout failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
