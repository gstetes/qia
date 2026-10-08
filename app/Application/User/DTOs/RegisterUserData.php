<?php

namespace App\Application\User\DTOs;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $email,
        #[\SensitiveParameter]
        public string $password,
    ) {}
}
