<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Notifications\WelcomeUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = User::create($request->validated());
        $accessToken = $user->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'data' => [
                'access_token' => $accessToken,
            ]
        ], 201);
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credential'
            ]);
        }

        $accessToken = Auth::user()->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'data' => [
                'access_token' => $accessToken,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return empty_object(204);
    }
}
