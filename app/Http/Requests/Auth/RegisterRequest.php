<?php

namespace App\Http\Requests\Auth;

use App\Application\User\DTOs\RegisterUserData;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Password::defaults()],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }

    public function toData(): RegisterUserData
    {
        return new RegisterUserData(
            name: $this->string('name')->trim()->value(),
            email: $this->string('email')->value(),
            password: $this->string('password')->value(),
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }
}
