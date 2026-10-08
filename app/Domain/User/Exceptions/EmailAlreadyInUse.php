<?php

namespace App\Domain\User\Exceptions;

use DomainException;

class EmailAlreadyInUse extends DomainException
{
    public static function for(string $email): self
    {
        return new self("The email [{$email}] is already in use.");
    }
}
