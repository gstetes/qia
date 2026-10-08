<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Contracts\UserRepository;
use App\Domain\User\Exceptions\EmailAlreadyInUse;
use App\Domain\User\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class EloquentUserRepository implements UserRepository
{
    public function create(string $name, string $email, string $password): User
    {
        try {
            return User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw EmailAlreadyInUse::for($email);
        }
    }
}
