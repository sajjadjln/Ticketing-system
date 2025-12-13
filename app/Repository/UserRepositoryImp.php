<?php

namespace App\Repository;

use App\Models\User;
use App\Interfaces\IUserRepository;
class UserRepositoryImp implements IUserRepository
{

    public function findByEmail($email): ?User
    {
        return User::where("email", $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}