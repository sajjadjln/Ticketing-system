<?php

namespace App\Interfaces;

use App\Models\User;
interface IUserRepository
{
    public function findByEmail($email): ?User;
    public function create(array $data): User;
}