<?php

namespace Tests\Feature\Accounts;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccountAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_update_own_profile_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
        $this->assertTrue(Gate::forUser($user)->allows('update', $user));
        $this->assertFalse(Gate::forUser($user)->allows('view', $other));
        $this->assertFalse(Gate::forUser($user)->allows('update', $other));
    }

    public function test_admin_can_manage_any_user_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('view', $user));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $user));
    }

    public function test_company_profile_is_visible_only_to_owner_and_admin(): void
    {
        $company = User::factory()->company()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $profile = $company->companyProfile;

        $this->assertTrue(Gate::forUser($company)->allows('update', $profile));
        $this->assertFalse(Gate::forUser($other)->allows('update', $profile));
        $this->assertFalse(Gate::forUser($other)->allows('view', $profile));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $profile));
    }

    public function test_admin_only_gates_are_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $broker = User::factory()->verifiedBroker()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('access-admin'));
        $this->assertTrue(Gate::forUser($admin)->allows('verify-accounts'));

        $this->assertFalse(Gate::forUser($user)->allows('access-admin'));
        $this->assertFalse(Gate::forUser($user)->allows('verify-accounts'));
        $this->assertFalse(Gate::forUser($broker)->allows('access-admin'));
        $this->assertFalse(Gate::forUser($broker)->allows('verify-accounts'));
    }

    public function test_publish_gate_requires_verified_broker_or_company(): void
    {
        $user = User::factory()->create();
        $pendingBroker = User::factory()->broker()->create();
        $verifiedBroker = User::factory()->verifiedBroker()->create();
        $pendingCompany = User::factory()->company()->create();
        $verifiedCompany = User::factory()->verifiedCompany()->create();
        $suspendedBroker = User::factory()->verifiedBroker()->create([
            'status' => AccountStatus::Suspended,
        ]);

        $this->assertFalse(Gate::forUser($user)->allows('publish-properties'));
        $this->assertFalse(Gate::forUser($pendingBroker)->allows('publish-properties'));
        $this->assertTrue(Gate::forUser($verifiedBroker)->allows('publish-properties'));
        $this->assertFalse(Gate::forUser($pendingCompany)->allows('publish-properties'));
        $this->assertTrue(Gate::forUser($verifiedCompany)->allows('publish-properties'));
        $this->assertFalse(Gate::forUser($suspendedBroker)->allows('publish-properties'));
    }

    public function test_account_type_middleware_blocks_wrong_account_types(): void
    {
        Route::middleware(['web', 'auth', 'account.type:company'])
            ->get('/__tests/company-only', fn (): string => 'company-ok');

        Route::middleware(['web', 'auth', 'account.type:broker,company'])
            ->get('/__tests/professional-only', fn (): string => 'professional-ok');

        $regular = User::factory()->create();
        $broker = User::factory()->verifiedBroker()->create();
        $company = User::factory()->verifiedCompany()->create();

        $this->actingAs($regular)->get('/__tests/company-only')->assertForbidden();
        $this->actingAs($broker)->get('/__tests/company-only')->assertForbidden();
        $this->actingAs($company)->get('/__tests/company-only')->assertOk();

        $this->actingAs($regular)->get('/__tests/professional-only')->assertForbidden();
        $this->actingAs($broker)->get('/__tests/professional-only')->assertOk();
        $this->actingAs($company)->get('/__tests/professional-only')->assertOk();
    }

    public function test_broker_profile_is_visible_only_to_owner_and_admin(): void
    {
        $broker = User::factory()->broker()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $profile = $broker->brokerProfile;

        $this->assertTrue(Gate::forUser($broker)->allows('view', $profile));
        $this->assertTrue(Gate::forUser($broker)->allows('update', $profile));
        $this->assertFalse(Gate::forUser($other)->allows('update', $profile));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $profile));
    }
}
