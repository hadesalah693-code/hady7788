<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_the_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('تسجيل الدخول')
            ->assertSee('تذكرني')
            ->assertSee('name="csrf-token"', false);
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'Password123',
            'last_login_at' => null,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'USER@example.com ')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertSame(__('app.auth.welcome_back', ['name' => $user->name]), session('status'));
    }

    public function test_login_regenerates_the_session_id(): void
    {
        $user = User::factory()->create([
            'email' => 'session@example.com',
            'password' => 'Password123',
        ]);

        $this->get(route('login'));
        $previousSessionId = session()->getId();

        Livewire::test(Login::class)
            ->set('email', 'session@example.com')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSessionId, session()->getId());
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'user@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_unknown_email_is_rejected(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'nobody@example.com')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_remember_me_issues_a_new_remember_token(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@example.com',
            'password' => 'Password123',
            'remember_token' => null,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'remember@example.com')
            ->set('password', 'Password123')
            ->set('remember', true)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_without_remember_me_does_not_issue_a_remember_token(): void
    {
        $user = User::factory()->create([
            'email' => 'no-remember@example.com',
            'password' => 'Password123',
            'remember_token' => null,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'no-remember@example.com')
            ->set('password', 'Password123')
            ->set('remember', false)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_suspended_accounts_cannot_log_in(): void
    {
        $user = User::factory()->suspended()->create([
            'email' => 'suspended@example.com',
            'password' => 'Password123',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'suspended@example.com')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasErrors(['email' => __('auth.suspended')]);

        $this->assertGuest();
        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'throttled@example.com',
            'password' => 'Password123',
        ]);

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Login::class)
                ->set('email', 'throttled@example.com')
                ->set('password', 'wrong-password')
                ->call('login')
                ->assertHasErrors(['email']);
        }

        Livewire::test(Login::class)
            ->set('email', 'throttled@example.com')
            ->set('password', 'Password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_requires_valid_email_and_password_fields(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'not-an-email')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email', 'password']);

        $this->assertGuest();
    }
}
