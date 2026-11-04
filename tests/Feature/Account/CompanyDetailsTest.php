<?php

namespace Tests\Feature\Account;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Livewire\Account\CompanyDetails;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_update_its_own_profile(): void
    {
        $company = User::factory()->company()->create();
        $profile = $company->companyProfile;

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->assertSet('company_name', $profile->company_name)
            ->set('company_name', 'شركة الإمداد للتطوير العقاري')
            ->set('commercial_register', 'CR-999999')
            ->set('tax_id', '999-999-999')
            ->set('website', 'https://emdad.test')
            ->set('address', 'القاهرة الجديدة')
            ->set('description', 'شركة متخصصة في التطوير العقاري.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', __('app.account.company.saved'));

        $profile->refresh();

        $this->assertSame('شركة الإمداد للتطوير العقاري', $profile->company_name);
        $this->assertSame('CR-999999', $profile->commercial_register);
        $this->assertSame('999-999-999', $profile->tax_id);
        $this->assertSame('https://emdad.test', $profile->website);
        $this->assertSame('القاهرة الجديدة', $profile->address);
    }

    public function test_profile_is_created_when_missing(): void
    {
        $company = User::factory()->company()->create();
        $company->companyProfile()->delete();

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->assertSet('company_name', $company->name)
            ->set('company_name', 'شركة جديدة')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_profiles', [
            'user_id' => $company->id,
            'company_name' => 'شركة جديدة',
        ]);
    }

    public function test_company_name_is_required(): void
    {
        $this->actingAs(User::factory()->company()->create());

        Livewire::test(CompanyDetails::class)
            ->set('company_name', '')
            ->call('save')
            ->assertHasErrors(['company_name' => 'required']);
    }

    public function test_website_must_be_a_valid_http_url(): void
    {
        $this->actingAs(User::factory()->company()->create());

        Livewire::test(CompanyDetails::class)
            ->set('website', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['website' => 'url']);

        Livewire::test(CompanyDetails::class)
            ->set('website', 'ftp://example.test')
            ->call('save')
            ->assertHasErrors(['website' => 'url']);
    }

    public function test_company_can_upload_a_valid_logo(): void
    {
        Storage::fake('public');

        $company = User::factory()->company()->create();

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->set('logo', UploadedFile::fake()->image('logo.png', 400, 400))
            ->call('save')
            ->assertHasNoErrors();

        $profile = $company->companyProfile->refresh();

        $this->assertNotNull($profile->logo_path);
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_logo_must_be_an_allowed_image_type(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->company()->create());

        Livewire::test(CompanyDetails::class)
            ->set('logo', UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['logo']);
    }

    public function test_logo_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->company()->create());

        Livewire::test(CompanyDetails::class)
            ->set('logo', UploadedFile::fake()->image('large.png', 400, 400)->size(3000))
            ->assertHasErrors(['logo' => 'max']);
    }

    public function test_replacing_the_logo_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('company-logos/old.png', 'old');

        $company = User::factory()->company()->create();
        $company->companyProfile->update(['logo_path' => 'company-logos/old.png']);

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->set('logo', UploadedFile::fake()->image('new.png', 400, 400))
            ->call('save')
            ->assertHasNoErrors();

        $profile = $company->companyProfile->refresh();

        Storage::disk('public')->assertMissing('company-logos/old.png');
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_company_can_remove_its_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('company-logos/old.png', 'old');

        $company = User::factory()->company()->create();
        $company->companyProfile->update(['logo_path' => 'company-logos/old.png']);

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->call('removeLogo')
            ->assertSet('currentLogo', null);

        $this->assertNull($company->companyProfile->refresh()->logo_path);
        Storage::disk('public')->assertMissing('company-logos/old.png');
    }

    public function test_changing_official_fields_after_verification_requires_reverification(): void
    {
        $company = User::factory()->verifiedCompany()->create();
        $profile = $company->companyProfile;

        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->set('commercial_register', 'CR-REVERIFY-1')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('reverificationTriggered', true);

        $profile->refresh();

        $this->assertSame(VerificationStatus::Pending, $profile->verification_status);
        $this->assertNull($profile->verified_at);
        $this->assertNull($profile->verified_by);
        $this->assertSame(AccountStatus::Active, $company->refresh()->status);
    }

    public function test_non_official_field_changes_keep_the_verified_status(): void
    {
        $company = User::factory()->verifiedCompany()->create();
        $profile = $company->companyProfile;

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->set('address', 'عنوان جديد للشركة')
            ->set('description', 'وصف جديد للشركة.')
            ->set('website', 'https://new-site.test')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('reverificationTriggered', false);

        $profile->refresh();

        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);
        $this->assertNotNull($profile->verified_at);
    }

    public function test_saving_company_data_never_updates_another_company(): void
    {
        $company = User::factory()->company()->create();
        $other = User::factory()->company()->create();
        $otherName = $other->companyProfile->company_name;

        $this->actingAs($company);

        Livewire::test(CompanyDetails::class)
            ->set('company_name', 'اسم الشركة الجديد')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($otherName, $other->companyProfile->refresh()->company_name);
        $this->assertSame('اسم الشركة الجديد', $company->companyProfile->refresh()->company_name);
    }
}
