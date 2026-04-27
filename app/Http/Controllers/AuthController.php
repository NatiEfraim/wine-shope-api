<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    //
    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'personal_id' => ['required', 'string', 'unique:users,personal_id'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $token = $user->createToken('personal-access-token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
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
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

    $tokenName = config('auth.token_name', 'access_token');

    $token = $user->createToken($tokenName);


    return response()
        ->json([
            'message' => 'User logged in successfully',
            'name' => $user->name,
            'personal_id' => $user->personal_id,
            'token_type' => 'Bearer',
        ])
        ->withCookie(
            Cookie::make(
                $tokenName,
                $token->accessToken,
                60 * 24, 
            )
        );
   
        } catch (\Exception $e) {
            Log::error('Error in AuthController: getUserDetails function: ' . $e->getMessage());
      
        }


    }

        /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ]);
    }
}
