<?php

namespace Tests\Feature\Services;

use App\Enums\ServiceRequestStatus;
use App\Livewire\Services\ServiceRequestForm;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceRequestCreationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ServiceCategory
    {
        return ServiceCategory::factory()->create(['name_ar' => 'سباكة']);
    }

    private function makeGovernorate(): Governorate
    {
        return Governorate::factory()->create(['name_ar' => 'القاهرة']);
    }

    private function validPayload(ServiceCategory $category, Governorate $governorate): array
    {
        return [
            'service_category_id' => $category->id,
            'governorate_id' => $governorate->id,
            'title' => 'تسريب مياه في الحمام',
            'description' => 'يوجد تسريب مياه أسفل حوض الحمام ويحتاج إلى إصلاح عاجل.',
            'address' => 'شارع التحرير، الدور الثالث، شقة 5',
            'contact_phone' => '01000000000',
        ];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('services.request'))->assertRedirect(route('login'));
    }

    public function test_suspended_user_cannot_open_the_request_form(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)
            ->get(route('services.request'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_create_a_request_without_choosing_a_provider(): void
    {
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $user = User::factory()->create(['phone' => '01000000000']);

        $this->actingAs($user);

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تسريب مياه في الحمام')
            ->set('description', 'يوجد تسريب مياه أسفل حوض الحمام ويحتاج إلى إصلاح عاجل.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->call('submit')
            ->assertHasNoErrors();

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->assertSame($user->id, $serviceRequest->user_id);
        $this->assertSame($category->id, $serviceRequest->service_category_id);
        $this->assertSame($governorate->id, $serviceRequest->governorate_id);
        $this->assertSame(ServiceRequestStatus::New, $serviceRequest->status);
        $this->assertNull($serviceRequest->service_provider_id);
        $this->assertNull($serviceRequest->assigned_at);
        $this->assertStringStartsWith('SR-', $serviceRequest->reference);
    }

    public function test_customer_can_choose_an_active_provider_and_the_request_is_assigned(): void
    {
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->create(['governorate_id' => $governorate->id]);
        $provider->categories()->attach($category);

        $user = User::factory()->create(['phone' => '01000000000']);

        $this->actingAs($user);

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('service_provider_id', $provider->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تركيب خلاط جديد')
            ->set('description', 'أحتاج إلى تركيب خلاط حوض جديد في الحمام الرئيسي.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->call('submit')
            ->assertHasNoErrors();

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->assertSame($provider->id, $serviceRequest->service_provider_id);
        $this->assertSame(ServiceRequestStatus::Assigned, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->assigned_at);
    }

    public function test_pending_provider_cannot_be_selected(): void
    {
        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $provider = ServiceProvider::factory()->pending()->create(['governorate_id' => $governorate->id]);
        $provider->categories()->attach($category);

        $this->actingAs(User::factory()->create(['phone' => '01000000000']));

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('service_provider_id', $provider->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تركيب خلاط جديد')
            ->set('description', 'أحتاج إلى تركيب خلاط حوض جديد في الحمام الرئيسي.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->call('submit')
            ->assertHasErrors(['service_provider_id']);
    }

    public function test_request_form_validates_required_fields(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ServiceRequestForm::class)
            ->call('submit')
            ->assertHasErrors([
                'service_category_id',
                'governorate_id',
                'title',
                'description',
                'address',
                'contact_phone',
            ]);
    }

    public function test_customer_can_upload_request_photos_to_the_private_disk(): void
    {
        Storage::fake('service-images');

        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();

        $this->actingAs(User::factory()->create(['phone' => '01000000000']));

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تسريب مياه في الحمام')
            ->set('description', 'يوجد تسريب مياه أسفل حوض الحمام ويحتاج إلى إصلاح عاجل.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->set('photos', [UploadedFile::fake()->image('leak.jpg', 400, 400)])
            ->call('submit')
            ->assertHasNoErrors();

        $image = ServiceRequestImage::query()->firstOrFail();

        $this->assertSame('service-images', $image->disk);
        Storage::disk('service-images')->assertExists($image->path);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('service-images');

        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();

        $this->actingAs(User::factory()->create(['phone' => '01000000000']));

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تسريب مياه في الحمام')
            ->set('description', 'يوجد تسريب مياه أسفل حوض الحمام ويحتاج إلى إصلاح عاجل.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->set('photos', [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
            ->call('submit')
            ->assertHasErrors(['photos.0']);

        $this->assertSame(0, ServiceRequest::query()->count());
    }

    public function test_customer_cannot_exceed_the_open_request_limit(): void
    {
        config(['service_module.requests.max_open_per_customer' => 1]);

        $category = $this->makeCategory();
        $governorate = $this->makeGovernorate();
        $user = User::factory()->create(['phone' => '01000000000']);

        ServiceRequest::factory()->create([
            'user_id' => $user->id,
            'service_category_id' => $category->id,
            'governorate_id' => $governorate->id,
            'status' => ServiceRequestStatus::New,
        ]);

        $this->actingAs($user);

        Livewire::test(ServiceRequestForm::class)
            ->set('service_category_id', $category->id)
            ->set('governorate_id', $governorate->id)
            ->set('title', 'تسريب مياه في الحمام')
            ->set('description', 'يوجد تسريب مياه أسفل حوض الحمام ويحتاج إلى إصلاح عاجل.')
            ->set('address', 'شارع التحرير، الدور الثالث، شقة 5')
            ->set('contact_phone', '01000000000')
            ->call('submit')
            ->assertHasErrors(['title']);

        $this->assertSame(1, ServiceRequest::query()->count());
    }
}
