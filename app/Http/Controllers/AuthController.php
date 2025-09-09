<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends BaseController
{
    /**
     * Register a new user account.
     *
     * Creates a user with a hashed password and marks their email as verified.
     *
     * @param  \App\Http\Requests\RegisterRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            return $this->sendResponse(new UserResource($user), 'User registered successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to register user', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Log in a user and issue an API token.
     *
     * Validates credentials, deletes any existing tokens for the user,
     * and creates a new API token.
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // revoke old tokens optionally
            $user->tokens()->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->sendResponse((new UserResource($user))->withToken($token), 'Login successful');
        } catch (Throwable $e) {
            return $this->sendError('Unable to log in', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Log out the authenticated user.
     *
     * Revokes the currently active access token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out']);
        } catch (Throwable $e) {
            return $this->sendError('Unable to log out', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the authenticated user profile.
     *
     * Loads the user along with their appointments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(): JsonResponse
    {
        try {
            $user = User::with('appointments')->find(Auth::id());
            return $this->sendResponse(new UserResource($user->load('appointments')), 'User profile fetched successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to fetch user profile', ['error' => $e->getMessage()], 500);
        }
    }
}
