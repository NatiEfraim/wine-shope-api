<?php

namespace App\Http\Controllers;

use App\Enum\RoleEnum;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeWineShopMail;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{


public function sendEmail()
{

      try {
     $user = User::first();

    Mail::to($user->email)->send(
        new WelcomeWineShopMail($user->name)
    );

    return response()->json([
        'message' => 'Email sent successfully'
    ]);

        } catch (\Throwable $e) {
            Log::error( $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch users',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

}

    /**
     * GET /api/users
     */
    public function index()
    {
        try {
            // Added eager loading of roles to prevent N+1 query problem
            $users = User::with('roles')->where('is_deleted', false)->get();

            return response()->json($users, Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('User index error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch users',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    

    /**
     * GET /api/users/{id}
     */
    public function show($id)
    {
        try {
            $user = User::with('roles')->where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($user, Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('User show error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/users/roles
     */
    public function roles()
    {
        try {
            $roles = Role::where('guard_name', 'passport')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => RoleEnum::labelFromId($role->id),
                    ];
                });

            return response()->json($roles, Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Roles fetch error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch roles',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/users
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->all();
            $roleId = $validated['role_id'];
            unset($validated['role_id']);
            $validated['password'] = Hash::make($validated['personal_id']);
            
            $user = User::create($validated);
            $user->assignRole($roleId);
            
            $tokenName = config('auth.token_name', 'access_token');
            $token = $user->createToken($tokenName)->accessToken;
            
            return response()->json([
                'message' => 'User created successfully',
                'data' => $user,
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            Log::error('User store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            // Basic validation for updatable fields
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role_id' => 'sometimes|integer|exists:roles,id',
            ]);

            // If a new role ID is sent, synchronize it
            if (isset($validated['role_id'])) {
                $user->syncRoles([$validated['role_id']]);
                unset($validated['role_id']); // Remove from validated data to avoid updating the users table
            }

            if (!empty($validated)) {
                $user->update($validated);
            }

            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('User update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        try {
            $user = User::where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $user->update(['is_deleted' => true]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('User delete error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}