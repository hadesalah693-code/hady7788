<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Security;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_their_password_with_the_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'new-password-2')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', __('app.account.security.saved'))
            ->assertSet('current_password', '')
            ->assertSet('password', '');

        $this->assertTrue(Hash::check('new-password-2', $user->refresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'wrong-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'new-password-2')
            ->call('updatePassword')
            ->assertHasErrors(['current_password' => __('auth.password')]);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }

    public function test_new_password_must_differ_from_the_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('same-password-1')]);

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'same-password-1')
            ->set('password', 'same-password-1')
            ->set('password_confirmation', 'same-password-1')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'different']);

        $this->assertTrue(Hash::check('same-password-1', $user->refresh()->password));
    }

    public function test_new_password_must_meet_the_default_password_rules(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'onlyletters')
            ->set('password_confirmation', 'onlyletters')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'other-password-3')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'confirmed']);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }

    public function test_repeated_wrong_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            Livewire::test(Security::class)
                ->set('current_password', 'wrong-password-1')
                ->set('password', 'new-password-2')
                ->set('password_confirmation', 'new-password-2')
                ->call('updatePassword')
                ->assertHasErrors(['current_password']);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts('password-update:'.$user->id, 5));

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'new-password-2')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }
}
