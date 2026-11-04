<?php

namespace Tests\Feature\Accounts;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use App\Models\BrokerProfile;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_user_is_regular_and_active(): void
    {
        $user = User::factory()->create();

        $this->assertSame(AccountType::User, $user->account_type);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertTrue($user->isRegularUser());
        $this->assertFalse($user->isBroker());
        $this->assertFalse($user->isCompany());
        $this->assertTrue($user->isVerifiedAccount());
    }

    public function test_broker_starts_pending_verification_with_professional_profile(): void
    {
        $broker = User::factory()->broker()->create();

        $this->assertSame(AccountType::Broker, $broker->account_type);
        $this->assertSame(AccountStatus::Pending, $broker->status);
        $this->assertFalse($broker->isVerifiedAccount());
        $this->assertFalse($broker->canPublishProperties());

        $this->assertInstanceOf(BrokerProfile::class, $broker->brokerProfile);
        $this->assertSame(VerificationStatus::Pending, $broker->brokerProfile->verification_status);
    }

    public function test_verified_broker_can_publish_properties(): void
    {
        $broker = User::factory()->verifiedBroker()->create();

        $this->assertSame(AccountStatus::Active, $broker->status);
        $this->assertTrue($broker->isVerifiedAccount());
        $this->assertTrue($broker->canPublishProperties());
        $this->assertTrue($broker->brokerProfile->verification_status->isVerified());
    }

    public function test_company_starts_pending_verification_with_company_profile(): void
    {
        $company = User::factory()->company()->create();

        $this->assertSame(AccountType::Company, $company->account_type);
        $this->assertSame(AccountStatus::Pending, $company->status);
        $this->assertFalse($company->isVerifiedAccount());
        $this->assertFalse($company->canPublishProperties());
        $this->assertInstanceOf(CompanyProfile::class, $company->companyProfile);
    }

    public function test_verified_company_can_publish_properties(): void
    {
        $company = User::factory()->verifiedCompany()->create();

        $this->assertTrue($company->isVerifiedAccount());
        $this->assertTrue($company->canPublishProperties());
    }

    public function test_suspended_verified_broker_cannot_publish_properties(): void
    {
        $broker = User::factory()->verifiedBroker()->suspended()->create();

        $this->assertTrue($broker->isSuspended());
        $this->assertFalse($broker->isVerifiedAccount());
        $this->assertFalse($broker->canPublishProperties());
    }

    public function test_admin_keeps_full_publishing_rights(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canPublishProperties());
        $this->assertTrue($admin->isVerifiedAccount());
    }

    public function test_broker_profile_can_be_linked_to_a_company(): void
    {
        $company = User::factory()->company()->create();
        $broker = User::factory()->broker()->create();

        $broker->brokerProfile->update(['company_id' => $company->companyProfile->id]);

        $this->assertTrue($company->companyProfile->brokers()->whereKey($broker->brokerProfile->id)->exists());
        $this->assertSame($company->companyProfile->id, $broker->brokerProfile->fresh()->company->id);
    }

    public function test_rejected_profile_is_not_verified_even_when_account_is_active(): void
    {
        $broker = User::factory()->broker()->create([
            'status' => AccountStatus::Active,
        ]);

        $broker->brokerProfile()->update([
            'verification_status' => VerificationStatus::Rejected,
        ]);

        $this->assertFalse($broker->fresh()->isVerifiedAccount());
        $this->assertFalse($broker->fresh()->canPublishProperties());
    }
}
