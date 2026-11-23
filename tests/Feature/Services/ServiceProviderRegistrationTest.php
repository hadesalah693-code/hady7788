<?php

namespace Tests\Feature\Services;

use App\Enums\ServiceProviderStatus;
use App\Livewire\Services\ProviderProfile;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceProviderRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ServiceCategory
    {
        return ServiceCategory::factory()->create(['name_ar' => 'سباكة', 'is_active' => true]);
    }

    private function makeGovernorate(): Governorate
    {
        return Governorate::factory()->create(['name_ar' => 'القاهرة']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('account.service-provider'))->assertRedirect(route('login'));
    }

    public function test_user_can_register_as_a_provider_and_starts_pending(): void
    {
        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'محمد للسباكة')
            ->set('phone', '01000000000')
            ->set('bio', 'خبرة طويلة في أعمال السباكة المنزلية.')
            ->set('experience_years', 8)
            ->set('governorate_id', $governorate->id)
            ->set('service_areas_text', 'مدينة نصر، مصر الجديدة')
            ->set('category_ids', [$category->id])
            ->call('save')
            ->assertHasNoErrors();

        $provider = ServiceProvider::query()->firstOrFail();

        $this->assertSame($user->id, $provider->user_id);
        $this->assertSame(ServiceProviderStatus::Pending, $provider->status);
        $this->assertTrue($provider->is_available);
        $this->assertSame(['مدينة نصر', 'مصر الجديدة'], $provider->service_areas);
        $this->assertTrue($provider->categories()->whereKey($category->id)->exists());
    }

    public function test_provider_registration_requires_at_least_one_specialty(): void
    {
        $user = User::factory()->create();
        $governorate = $this->makeGovernorate();

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'محمد للسباكة')
            ->set('phone', '01000000000')
            ->set('experience_years', 8)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [])
            ->call('save')
            ->assertHasErrors(['category_ids']);

        $this->assertSame(0, ServiceProvider::query()->count());
    }

    public function test_provider_registration_validates_phone_format(): void
    {
        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'محمد للسباكة')
            ->set('phone', 'not-a-phone')
            ->set('experience_years', 8)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->call('save')
            ->assertHasErrors(['phone']);

        $this->assertSame(0, ServiceProvider::query()->count());
    }

    public function test_suspended_user_cannot_save_a_provider_profile(): void
    {
        $user = User::factory()->suspended()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'محمد للسباكة')
            ->set('phone', '01000000000')
            ->set('experience_years', 8)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->call('save')
            ->assertForbidden();

        $this->assertSame(0, ServiceProvider::query()->count());
    }

    public function test_user_with_an_existing_provider_profile_cannot_register_again(): void
    {
        $user = User::factory()->create();
        ServiceProvider::factory()->create(['user_id' => $user->id]);

        $this->assertFalse(
            Gate::forUser($user)->allows('create', ServiceProvider::class)
        );
    }

    public function test_rejected_provider_resubmission_returns_to_pending(): void
    {
        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => ServiceProviderStatus::Rejected,
            'rejection_reason' => 'بيانات غير مكتملة',
        ]);
        $provider->categories()->attach($category);

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'محمد للسباكة')
            ->set('phone', '01000000000')
            ->set('experience_years', 10)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->call('save')
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(ServiceProviderStatus::Pending, $provider->status);
        $this->assertNull($provider->rejection_reason);
    }

    public function test_active_provider_stays_active_after_updating_the_profile(): void
    {
        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => ServiceProviderStatus::Active,
        ]);
        $provider->categories()->attach($category);

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', 'اسم محدث')
            ->set('phone', '01000000000')
            ->set('experience_years', 12)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->call('save')
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(ServiceProviderStatus::Active, $provider->status);
        $this->assertSame('اسم محدث', $provider->display_name);
    }

    public function test_provider_can_upload_portfolio_images_to_the_public_disk(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => ServiceProviderStatus::Pending,
        ]);
        $provider->categories()->attach($category);

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', $provider->display_name)
            ->set('phone', $provider->phone)
            ->set('experience_years', 5)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->set('photos', [UploadedFile::fake()->image('work.jpg', 500, 500)])
            ->call('save')
            ->assertHasNoErrors();

        $image = ServiceProviderImage::query()->firstOrFail();

        $this->assertSame($provider->id, $image->service_provider_id);
        $this->assertSame('public', $image->disk);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_provider_portfolio_rejects_non_image_uploads(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => ServiceProviderStatus::Pending,
        ]);
        $provider->categories()->attach($category);

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->set('display_name', $provider->display_name)
            ->set('phone', $provider->phone)
            ->set('experience_years', 5)
            ->set('governorate_id', $governorate->id)
            ->set('category_ids', [$category->id])
            ->set('photos', [UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['photos.0']);

        $this->assertSame(0, ServiceProviderImage::query()->count());
    }

    public function test_provider_can_remove_a_portfolio_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = $this->makeCategory();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'status' => ServiceProviderStatus::Pending,
        ]);
        $provider->categories()->attach($category);

        $path = 'service-providers/'.$provider->id.'/work.jpg';
        Storage::disk('public')->put($path, 'image-bytes');

        $image = ServiceProviderImage::query()->create([
            'service_provider_id' => $provider->id,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 11,
            'sort_order' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(ProviderProfile::class)
            ->call('removeImage', $image->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('service_provider_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_another_user_cannot_remove_someone_elses_portfolio_image(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $category = $this->makeCategory();
        $provider = ServiceProvider::factory()->create([
            'user_id' => $owner->id,
            'status' => ServiceProviderStatus::Active,
        ]);
        $provider->categories()->attach($category);

        $image = ServiceProviderImage::query()->create([
            'service_provider_id' => $provider->id,
            'path' => 'service-providers/'.$provider->id.'/work.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 11,
            'sort_order' => 0,
        ]);

        // The other user has no provider profile, so the page renders the
        // registration form instead of the owner's gallery.
        $this->actingAs($other);

        Livewire::test(ProviderProfile::class)
            ->call('removeImage', $image->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_provider_images', ['id' => $image->id]);
    }
}
