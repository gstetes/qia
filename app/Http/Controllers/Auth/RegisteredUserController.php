<?php

namespace App\Http\Controllers\Auth;

use App\Application\User\UseCases\RegisterUser;
use App\Domain\User\Exceptions\EmailAlreadyInUse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        try {
            $registerUser->handle($request->toData());
        } catch (EmailAlreadyInUse) {
            throw ValidationException::withMessages([
                'email' => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
            ]);
        }

        Inertia::flash('status', 'registered');

        return redirect()->route('login');
    }
}
