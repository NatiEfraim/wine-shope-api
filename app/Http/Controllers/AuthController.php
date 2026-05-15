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
use App\Mail\WelcomeWineShopMail;
use Illuminate\Support\Facades\Mail;

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
             $this->sendWelcomeWineShopEmail($user);
            return response()
                ->json(
                    [
                        'message' => 'User registered successfully',
                        'name' => $user->name,
                        'personal_id' => $user->personal_id,
                        // 'token_type' => 'Bearer',
                    ],
                    Response::HTTP_CREATED,
                )
                ->withCookie(
                    Cookie::make(
                        $tokenName,
                        $token->accessToken,
                        60 * 24,
                        '/', // path
                        null, // domain
                        false, // secure
                        false, // httpOnly
                    ),
                );
        } catch (\Throwable $e) {
            Log::error('Register error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Registration failed',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request)
    {
        try {
    
            $validated = $request->validated();
    
            $user = User::where('email', $validated['email'])->first();
    
            $passwordCheck = Hash::check($validated['password'], $user->password);

            if (!$user || !$passwordCheck) {
                return response()->json(
                    [
                        'message' => 'Invalid credentials',
                    ],
                    Response::HTTP_UNAUTHORIZED,
                );
            }


            $tokenName = config('auth.token_name', 'access_token');
            $token = $user->createToken($tokenName);


            $response = response()
                ->json(
                    [
                        'message' => 'User logged in successfully',
                        'name' => $user->name,
                        'personal_id' => $user->personal_id,
                        // 'token_type' => 'Bearer',
                    ],
                    Response::HTTP_OK,
                )
                ->withCookie(
                    Cookie::make(
                        $tokenName,
                        $token->accessToken,
                        60 * 24,
                        '/', // path
                        null, // domain
                        false, // secure
                        false, // httpOnly
                    ),
                );

        
            return $response;
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Login failed',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
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
                return response()->json(
                    [
                        'message' => 'Old password is incorrect',
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json(
                [
                    'message' => 'Password changed successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to change password',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(
                    [
                        'message' => 'Unauthorized',
                    ],
                    Response::HTTP_UNAUTHORIZED,
                );
            }

            $request->user()->token()->revoke();

            $tokenName = config('auth.token_name', 'access_token');

            return response()
                ->json(
                    [
                        'message' => 'User logged out successfully',
                    ],
                    Response::HTTP_OK,
                )
                ->withCookie(Cookie::forget($tokenName, '/', 'localhost'));
        } catch (\Throwable $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Logout failed',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
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
                return response()->json(
                    [
                        'message' => 'Unauthorized',
                    ],
                    Response::HTTP_UNAUTHORIZED,
                );
            }

            $user->load('roles');

            return response()->json(
                [
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'personal_id' => $user->personal_id,
                        'phone' => $user->phone,
                        'role' => $user->roles
                            ->map(function ($role) {
                                return [
                                    'id' => $role->id,
                                    'name' => RoleEnum::labelFromId($role->id),
                                ];
                            })
                            ->values(),
                    ],
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Auth user error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to fetch authenticated user',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function sendWelcomeWineShopEmail(User $user)
    {
        try {
            Mail::to($user->email)->send(new WelcomeWineShopMail($user->name));
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
