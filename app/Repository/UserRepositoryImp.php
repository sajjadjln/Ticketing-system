<?php

namespace App\Repository;

use App\Models\User;
use App\Interfaces\UserRepositoryContract;
class UserRepositoryImp implements UserRepositoryContract
{

    public function findByEmail(string $email): ?User
    {
        return User::whereEmail($email)->first();
    }

    public function create(array $data): User
    {
        return User::create([
            "email" => $data['email'],
            "password" => $data['password'],
            "name" => $data['name'],
            "role" => "user",
        ]);
    }
}