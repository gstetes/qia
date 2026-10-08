<?php

namespace Tests\Feature\Auth;

use App\Domain\User\Contracts\UserRepository;
use App\Domain\User\Exceptions\EmailAlreadyInUse;
use App\Domain\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
    }

    public function test_user_can_register_and_is_redirected_to_login(): void
    {
        Event::fake([Registered::class]);

        $this->post(route('register'), [
            'name' => '  Maria Silva  ',
            'email' => ' Maria@Empresa.com.br ',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])
            ->assertRedirect(route('login'))
            ->assertInertiaFlash('status', 'registered');

        $user = User::where('email', 'maria@empresa.com.br')->firstOrFail();

        $this->assertSame('Maria Silva', $user->name);
        $this->assertTrue(Hash::check('senha-segura', $user->password));
        $this->assertGuest();
        Event::assertDispatched(Registered::class);
    }

    public function test_registration_requires_valid_data(): void
    {
        $this->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['name', 'email', 'password', 'password_confirmation']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'maria@empresa.com.br']);

        $this->post(route('register'), [
            'name' => 'Maria Silva',
            'email' => 'MARIA@empresa.com.br',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_concurrent_registration_with_same_email_returns_validation_error(): void
    {
        $this->app->bind(UserRepository::class, fn () => new class implements UserRepository
        {
            public function create(string $name, string $email, string $password): User
            {
                throw EmailAlreadyInUse::for($email);
            }
        });

        $this->post(route('register'), [
            'name' => 'Maria Silva',
            'email' => 'maria@empresa.com.br',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_messages_are_translated_to_portuguese(): void
    {
        app()->setLocale('pt_BR');

        User::factory()->create(['email' => 'maria@empresa.com.br']);

        $this->post(route('register'), [
            'name' => '',
            'email' => 'maria@empresa.com.br',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors([
            'name' => 'O campo nome é obrigatório.',
            'email' => 'Este e-mail já está em uso.',
            'password' => 'O campo senha deve ter pelo menos 8 caracteres.',
            'password_confirmation' => 'As senhas não conferem.',
        ]);
    }

    public function test_authenticated_user_cannot_access_register(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('register'))
            ->assertRedirect(route('home', absolute: false));
    }
}
