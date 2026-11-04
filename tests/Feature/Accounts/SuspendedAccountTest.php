<?php

namespace Tests\Feature\Accounts;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Services\AccountSuspensionService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuspendedAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspending_an_account_revokes_remember_token_and_sessions(): void
    {
        $user = User::factory()->verifiedBroker()->create([
            'remember_token' => 'active-remember-token',
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-one',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode('payload'),
            'last_activity' => now()->timestamp,
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-two',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.2',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode('payload'),
            'last_activity' => now()->timestamp,
        ]);

        app(AccountSuspensionService::class)->suspend($user);

        $user->refresh();

        $this->assertSame(AccountStatus::Suspended, $user->status);
        $this->assertNull($user->remember_token);
        $this->assertFalse($user->canPublishProperties());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }

    public function test_suspension_only_deletes_the_target_users_sessions(): void
    {
        $suspended = User::factory()->create();
        $other = User::factory()->create();

        DB::table('sessions')->insert([
            [
                'id' => 'target-session',
                'user_id' => $suspended->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => base64_encode('payload'),
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'other-session',
                'user_id' => $other->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => base64_encode('payload'),
                'last_activity' => now()->timestamp,
            ],
        ]);

        app(AccountSuspensionService::class)->suspend($suspended);

        $this->assertSame(0, DB::table('sessions')->where('user_id', $suspended->id)->count());
        $this->assertSame(1, DB::table('sessions')->where('user_id', $other->id)->count());
    }

    public function test_an_existing_session_of_a_suspended_account_is_logged_out(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_suspended_admin_cannot_access_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->suspended()->create();

        $this->assertFalse($admin->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_reactivating_a_verified_professional_restores_active_status(): void
    {
        $broker = User::factory()->verifiedBroker()->suspended()->create();

        app(AccountSuspensionService::class)->reactivate($broker);

        $this->assertSame(AccountStatus::Active, $broker->fresh()->status);
    }

    public function test_reactivating_an_unverified_professional_returns_to_pending(): void
    {
        $broker = User::factory()->broker()->suspended()->create();

        $this->assertSame(VerificationStatus::Pending, $broker->brokerProfile->verification_status);

        app(AccountSuspensionService::class)->reactivate($broker);

        $this->assertSame(AccountStatus::Pending, $broker->fresh()->status);
        $this->assertFalse($broker->fresh()->canPublishProperties());
    }

    public function test_reactivating_a_regular_user_restores_active_status(): void
    {
        $user = User::factory()->suspended()->create();

        app(AccountSuspensionService::class)->reactivate($user);

        $this->assertSame(AccountStatus::Active, $user->fresh()->status);
    }
}
