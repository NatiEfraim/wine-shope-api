<?php

namespace App\Http\Controllers;

use App\Enum\RoleEnum;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangePasswordRequest;



class AuthController extends Controller
{
    //
    /**
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request)
    {
    try {

            $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->assignRole(RoleEnum::USER->value);

        $tokenName = config('auth.token_name', 'access_token');
        $token = $user->createToken($tokenName);

        return response()
            ->json([
                'message' => 'User registered successfully',
                'name' => $user->name,
                'personal_id' => $user->personal_id,
                // 'token_type' => 'Bearer',
            ], Response::HTTP_CREATED)
            ->withCookie(
                Cookie::make(
                    $tokenName,
                    $token->accessToken,
                    60 * 24,
                    '/', // path
                    null, // domain
                    false, // secure
                    false // httpOnly
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
    public function login(LoginRequest $request)
    {

 try {
        Log::info('Login request method: ' . $request->method());
        Log::info('Login request headers: ' . json_encode($request->headers->all()));
        Log::info('Login attempt for email: ' . $request->email);
        $validated = $request->validated();
        Log::info('Password received: ' . $validated['password']);

        $user = User::where('email', $validated['email'])->first();
        Log::info('User found: ' . ($user ? 'yes' : 'no'));

        $passwordCheck = Hash::check($validated['password'], $user->password);
        Log::info('Password check result: ' . ($passwordCheck ? 'true' : 'false'));

        if (!$user || !$passwordCheck) {
            Log::info('Invalid credentials for email: ' . $validated['email']);
            return response()->json([
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        Log::info('Login successful for email: ' . $validated['email']);

        $tokenName = config('auth.token_name', 'access_token');
        $token = $user->createToken($tokenName);

        Log::info('Token created: ' . $token->accessToken);

        $response = response()
            ->json([
                'message' => 'User logged in successfully',
                'name' => $user->name,
                'personal_id' => $user->personal_id,
                // 'token_type' => 'Bearer',
            ], Response::HTTP_OK)
            ->withCookie(
                Cookie::make(
                    $tokenName,
                    $token->accessToken,
                    60 * 24,
                    '/', // path
                    null, // domain
                    false, // secure
                    false // httpOnly
                )
            );

        Log::info('Response status: ' . $response->getStatusCode());
        return $response;

        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
 * POST /api/auth/change-password
 */
public function changePassword(ChangePasswordRequest $request)
{
    try {

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Old password is incorrect',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ], Response::HTTP_OK);

    } catch (\Throwable $e) {
        Log::error('Change password error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to change password',
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
                Cookie::forget($tokenName, '/', 'localhost')
            );

        } catch (\Throwable $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Logout failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
 * POST /api/auth/user
 */
public function user(Request $request)
{
    try {

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->load('roles');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'personal_id' => $user->personal_id,
                'phone' => $user->phone,
                'role' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => RoleEnum::labelFromId($role->id),
                    ];
                })->values(),
            ],
        ], Response::HTTP_OK);

    } catch (\Throwable $e) {
        dd($e->getMessage());
        Log::error('Auth user error: ' . $e->getMessage());

        return response()->json([
            'message' => 'Failed to fetch authenticated user',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}
