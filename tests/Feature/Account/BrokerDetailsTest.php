<?php

namespace Tests\Feature\Account;

use App\Enums\VerificationStatus;
use App\Livewire\Account\BrokerDetails;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrokerDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_can_update_their_own_profile_and_service_areas(): void
    {
        $cairo = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $giza = Governorate::factory()->create(['name_ar' => 'الجيزة']);

        $broker = User::factory()->broker()->create();

        $this->actingAs($broker);

        Livewire::test(BrokerDetails::class)
            ->set('professional_title', 'وسيط عقاري معتمد')
            ->set('license_number', 'BR-999999')
            ->set('years_of_experience', 7)
            ->set('bio', 'خبرة في التسويق العقاري.')
            ->set('service_areas', [$cairo->name_ar, $giza->name_ar])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', __('app.account.broker.saved'));

        $profile = $broker->brokerProfile->refresh();

        $this->assertSame('وسيط عقاري معتمد', $profile->professional_title);
        $this->assertSame('BR-999999', $profile->license_number);
        $this->assertSame(7, $profile->years_of_experience);
        $this->assertSame('خبرة في التسويق العقاري.', $profile->bio);
        $this->assertSame([$cairo->name_ar, $giza->name_ar], $profile->service_areas);
    }

    public function test_service_areas_must_be_known_governorates(): void
    {
        Governorate::factory()->create(['name_ar' => 'القاهرة']);

        $this->actingAs(User::factory()->broker()->create());

        Livewire::test(BrokerDetails::class)
            ->set('service_areas', ['محافظة غير موجودة'])
            ->call('save')
            ->assertHasErrors(['service_areas.0']);
    }

    public function test_years_of_experience_must_be_within_a_sensible_range(): void
    {
        $this->actingAs(User::factory()->broker()->create());

        Livewire::test(BrokerDetails::class)
            ->set('years_of_experience', -1)
            ->call('save')
            ->assertHasErrors(['years_of_experience' => 'min']);

        Livewire::test(BrokerDetails::class)
            ->set('years_of_experience', 71)
            ->call('save')
            ->assertHasErrors(['years_of_experience' => 'max']);
    }

    public function test_profile_is_created_when_missing(): void
    {
        $broker = User::factory()->broker()->create();
        $broker->brokerProfile()->delete();

        $this->actingAs($broker);

        Livewire::test(BrokerDetails::class)
            ->set('professional_title', 'وسيط جديد')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('broker_profiles', [
            'user_id' => $broker->id,
            'professional_title' => 'وسيط جديد',
        ]);
    }

    public function test_changing_the_license_after_verification_requires_reverification(): void
    {
        $broker = User::factory()->verifiedBroker()->create();
        $profile = $broker->brokerProfile;

        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);

        $this->actingAs($broker);

        Livewire::test(BrokerDetails::class)
            ->set('license_number', 'BR-REVERIFY-1')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('reverificationTriggered', true);

        $profile->refresh();

        $this->assertSame(VerificationStatus::Pending, $profile->verification_status);
        $this->assertNull($profile->verified_at);
        $this->assertNull($profile->verified_by);
    }

    public function test_non_official_field_changes_keep_the_verified_status(): void
    {
        $cairo = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $broker = User::factory()->verifiedBroker()->create();
        $profile = $broker->brokerProfile;

        $this->actingAs($broker);

        Livewire::test(BrokerDetails::class)
            ->set('professional_title', 'مستشار عقاري')
            ->set('years_of_experience', 12)
            ->set('bio', 'نبذة محدّثة.')
            ->set('service_areas', [$cairo->name_ar])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('reverificationTriggered', false);

        $profile->refresh();

        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);
        $this->assertNotNull($profile->verified_at);
    }

    public function test_company_affiliation_is_shown_as_read_only_data(): void
    {
        $company = User::factory()->verifiedCompany()->create();
        $company->companyProfile->update(['company_name' => 'شركة الإمداد العقارية']);

        $broker = User::factory()->verifiedBroker()->create();
        $broker->brokerProfile->update(['company_id' => $company->companyProfile->id]);

        $this->actingAs($broker);

        $this->get(route('account.broker'))
            ->assertOk()
            ->assertSee('شركة الإمداد العقارية')
            ->assertSee(__('app.account.broker.company'));

        Livewire::test(BrokerDetails::class)
            ->set('professional_title', 'وسيط تابع للشركة')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            $company->companyProfile->id,
            $broker->brokerProfile->refresh()->company_id,
        );
    }

    public function test_saving_broker_data_never_updates_another_broker(): void
    {
        $broker = User::factory()->broker()->create();
        $other = User::factory()->broker()->create();
        $otherTitle = $other->brokerProfile->professional_title;

        $this->actingAs($broker);

        Livewire::test(BrokerDetails::class)
            ->set('professional_title', 'المسمى الجديد')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($otherTitle, $other->brokerProfile->refresh()->professional_title);
        $this->assertSame('المسمى الجديد', $broker->brokerProfile->refresh()->professional_title);
    }
}
