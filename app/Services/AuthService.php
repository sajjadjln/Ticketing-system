<?php

namespace App\Services;

use App\Interfaces\IAuthService;
use App\Interfaces\IUserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements IAuthService
{
    protected $userRepository;
    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'token' => $token,
            'user' => $user,
            'tokenType' => 'Bearer'
        ];
    }

    public function register(string $username, string $password, string $email): array
    {
        $user = $this->userRepository->create(
            [
                'name' => $username,
                'password' => Hash::make($password),
                'email' => $email,
                'role' => 'user'
            ]
        );
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'token' => $token,
            'user' => $user,
            'token_type' => 'Bearer'
        ];
    }
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}