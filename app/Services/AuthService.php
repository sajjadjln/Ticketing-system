<?php

namespace App\Services;

use App\Interfaces\AuthServiceContract;
use App\Interfaces\UserRepositoryContract;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceContract
{
    public function __construct(
        protected readonly UserRepositoryContract $userRepository
    ) {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return $this->generateAuthResponse($user);
    }

    public function register(string $username, string $password, string $email): array
    {
        $user = $this->userRepository->create(
            [
                'name' => $username,
                'password' => $password,
                'email' => $email,
                'role' => 'user'
            ]
        );
        return $this->generateAuthResponse($user);
    }
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function generateAuthResponse(User $user, string $tokenName = 'auth_token'): array
    {
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'token_type' => 'Bearer'
        ];
    }
}