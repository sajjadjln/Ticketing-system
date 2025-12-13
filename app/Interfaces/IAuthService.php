<?php

namespace App\Interfaces;

use App\Models\User;
interface IAuthService
{
    public function login(string $email, string $password): array;
    public function logout(User $user): void;
    public function register(string $username, string $password, string $email): array;
}