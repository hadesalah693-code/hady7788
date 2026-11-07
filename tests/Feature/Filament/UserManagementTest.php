<?php

namespace Tests\Feature\Filament;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_users_management_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('المستخدمون');
    }

    public function test_non_admin_cannot_open_the_users_management_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_verify_a_broker(): void
    {
        $admin = User::factory()->admin()->create();
        $broker = User::factory()->broker()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('verify', $broker)
            ->assertHasNoErrors();

        $broker->refresh();

        $this->assertSame(VerificationStatus::Verified, $broker->brokerProfile->verification_status);
        $this->assertNotNull($broker->brokerProfile->verified_at);
        $this->assertSame($admin->id, $broker->brokerProfile->verified_by);
        $this->assertSame(AccountStatus::Active, $broker->status);
    }

    public function test_admin_can_verify_a_company(): void
    {
        $admin = User::factory()->admin()->create();
        $company = User::factory()->company()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('verify', $company)
            ->assertHasNoErrors();

        $company->refresh();

        $this->assertSame(VerificationStatus::Verified, $company->companyProfile->verification_status);
        $this->assertNotNull($company->companyProfile->verified_at);
        $this->assertSame($admin->id, $company->companyProfile->verified_by);
    }

    public function test_admin_can_reject_verification(): void
    {
        $admin = User::factory()->admin()->create();
        $broker = User::factory()->verifiedBroker()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('rejectVerification', $broker)
            ->assertHasNoErrors();

        $broker->refresh();

        $this->assertSame(VerificationStatus::Rejected, $broker->brokerProfile->verification_status);
        $this->assertNull($broker->brokerProfile->verified_at);
        $this->assertNull($broker->brokerProfile->verified_by);
    }

    public function test_admin_can_suspend_and_reactivate_an_account(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('suspend', $user)
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame(AccountStatus::Suspended, $user->status);

        Livewire::test(ListUsers::class)
            ->callTableAction('reactivate', $user)
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame(AccountStatus::Active, $user->status);
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertActionHidden(\\Filament\\Actions\\Testing\\TestAction::make('suspend')->table($admin));

        $admin->refresh();
        $this->assertNotSame(AccountStatus::Suspended, $admin->status);
    }

    public function test_verified_status_is_required_for_publishing_after_rejection(): void
    {
        $admin = User::factory()->admin()->create();
        $broker = User::factory()->verifiedBroker()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('rejectVerification', $broker)
            ->assertHasNoErrors();

        $broker->refresh();

        $this->assertFalse($broker->canPublishProperties());
    }
}
