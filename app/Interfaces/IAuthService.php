<?php

namespace App\Interfaces;

use App\Models\User;
interface IAuthService
{
    public function login($email, $password): array;
    public function logout(User $user): void;
    public function register($username, $password, $email): array;
}