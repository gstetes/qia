<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\RegisterUserData;
use App\Domain\User\Contracts\UserRepository;
use App\Domain\User\Exceptions\EmailAlreadyInUse;
use App\Domain\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Events\Dispatcher;

class RegisterUser
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Dispatcher $events,
    ) {}

    /**
     * @throws EmailAlreadyInUse
     */
    public function handle(RegisterUserData $data): User
    {
        $user = $this->users->create($data->name, $data->email, $data->password);

        $this->events->dispatch(new Registered($user));

        return $user;
    }
}
