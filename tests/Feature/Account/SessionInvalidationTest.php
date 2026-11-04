<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Security;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SessionInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('session.driver', 'database');
    }

    public function test_password_change_keeps_current_session_and_deletes_other_sessions(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user);

        $otherSessionId = 'other-session-id-456';
        $currentSessionId = session()->getId();

        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'current-device',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => $otherSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'other-device',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this->assertDatabaseHas('sessions', ['id' => $otherSessionId]);
        $this->assertDatabaseHas('sessions', ['id' => $currentSessionId]);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'new-password-2')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', __('app.account.security.saved'));

        $this->assertDatabaseMissing('sessions', ['id' => $otherSessionId]);

        // The seeded current session row is preserved (not deleted).
        $this->assertDatabaseHas('sessions', [
            'id' => $currentSessionId,
            'user_id' => $user->id,
        ]);

        // The current device remains authenticated afterwards.
        $this->get(route('account.dashboard'))->assertOk();
    }

    public function test_password_change_invalidates_remember_me_tokens(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $oldRememberToken = $user->remember_token;

        $this->actingAs($user);

        Livewire::test(Security::class)
            ->set('current_password', 'old-password-1')
            ->set('password', 'new-password-2')
            ->set('password_confirmation', 'new-password-2')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNotSame($oldRememberToken, $user->remember_token);
        $this->assertTrue(Hash::check('new-password-2', $user->password));
    }
}
