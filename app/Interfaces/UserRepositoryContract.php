<?php

namespace App\Interfaces;

use App\Models\User;
interface UserRepositoryContract
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
}