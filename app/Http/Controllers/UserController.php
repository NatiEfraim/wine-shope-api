<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    //
        /**
     * GET /api/users
     */
    public function index()
    {
              try {
            $users = User::where('is_deleted', false)->get();

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
            $user = User::where('is_deleted', false)
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
     * POST /api/users
     */
    public function store(Request $request)
    {
        try {
       $validated = $request->validated();

        $user = User::create($validated);

        $token = $user->createToken('api-token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
            'token' => $token
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

        $user->update($request->validated());

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
