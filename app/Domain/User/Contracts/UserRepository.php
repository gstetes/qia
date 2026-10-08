<?php

namespace App\Domain\User\Contracts;

use App\Domain\User\Exceptions\EmailAlreadyInUse;
use App\Domain\User\Models\User;

interface UserRepository
{
    /**
     * @throws EmailAlreadyInUse
     */
    public function create(string $name, string $email, string $password): User;
}
