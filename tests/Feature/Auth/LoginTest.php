<?php

namespace Tests\Feature\Auth;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_authenticated_user_is_redirected_from_login_to_home(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('home', absolute: false));
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_messages_are_translated_to_portuguese(): void
    {
        app()->setLocale('pt_BR');

        $user = User::factory()->create();

        $this->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors([
                'email' => 'O campo e-mail é obrigatório.',
                'password' => 'O campo senha é obrigatório.',
            ]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);
    }

    public function test_locale_is_shared_with_the_frontend(): void
    {
        app()->setLocale('pt_BR');

        $this->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'pt-BR'));
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
