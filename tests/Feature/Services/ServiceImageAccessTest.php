<?php

namespace Tests\Feature\Services;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceImageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('service-images');
    }

    private function makeRequest(?User $owner = null, ?ServiceProvider $provider = null): ServiceRequest
    {
        return ServiceRequest::factory()->assigned()->create([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'service_provider_id' => ($provider ?? ServiceProvider::factory()->create())->id,
        ]);
    }

    private function makeImage(ServiceRequest $request, array $attributes = []): ServiceRequestImage
    {
        $path = 'service-requests/'.$request->id.'/'.Str::uuid().'.jpg';

        Storage::disk('service-images')->put($path, 'image-bytes');

        return ServiceRequestImage::query()->create(array_merge([
            'service_request_id' => $request->id,
            'path' => $path,
            'disk' => 'service-images',
            'mime_type' => 'image/jpeg',
            'size' => 11,
        ], $attributes));
    }

    public function test_guests_cannot_access_private_request_images(): void
    {
        $request = $this->makeRequest();
        $image = $this->makeImage($request);

        $this->get(route('service-request-images.show', $image))->assertNotFound();
    }

    public function test_customer_can_access_their_own_request_images(): void
    {
        $owner = User::factory()->create();
        $request = $this->makeRequest($owner);
        $image = $this->makeImage($request);

        $response = $this->actingAs($owner)
            ->get(route('service-request-images.show', $image))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'image/jpeg');

        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_assigned_provider_can_access_request_images(): void
    {
        $providerUser = User::factory()->create();
        $provider = ServiceProvider::factory()->create(['user_id' => $providerUser->id]);
        $request = $this->makeRequest(provider: $provider);
        $image = $this->makeImage($request);

        $this->actingAs($providerUser)
            ->get(route('service-request-images.show', $image))
            ->assertOk();
    }

    public function test_admin_can_access_any_request_image(): void
    {
        $request = $this->makeRequest();
        $image = $this->makeImage($request);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('service-request-images.show', $image))
            ->assertOk();
    }

    public function test_another_customer_cannot_access_request_images(): void
    {
        $request = $this->makeRequest();
        $image = $this->makeImage($request);
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('service-request-images.show', $image))
            ->assertForbidden();
    }

    public function test_unassigned_provider_cannot_access_request_images(): void
    {
        $request = $this->makeRequest();
        $image = $this->makeImage($request);

        $otherProviderUser = User::factory()->create();
        ServiceProvider::factory()->create(['user_id' => $otherProviderUser->id]);

        $this->actingAs($otherProviderUser)
            ->get(route('service-request-images.show', $image))
            ->assertForbidden();
    }

    public function test_missing_image_file_returns_not_found(): void
    {
        $owner = User::factory()->create();
        $request = $this->makeRequest($owner);

        $image = ServiceRequestImage::query()->create([
            'service_request_id' => $request->id,
            'path' => 'service-requests/'.$request->id.'/missing.jpg',
            'disk' => 'service-images',
            'mime_type' => 'image/jpeg',
            'size' => 11,
        ]);

        $this->actingAs($owner)
            ->get(route('service-request-images.show', $image))
            ->assertNotFound();
    }

    public function test_images_of_a_soft_deleted_request_are_not_reachable(): void
    {
        $owner = User::factory()->create();
        $request = $this->makeRequest($owner);
        $image = $this->makeImage($request);

        $request->delete();

        $this->actingAs($owner)
            ->get(route('service-request-images.show', $image))
            ->assertNotFound();
    }

    public function test_request_images_are_not_written_to_the_public_disk(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $request = $this->makeRequest($owner);
        $image = $this->makeImage($request);

        $this->assertSame('service-images', $image->disk);
        Storage::disk('public')->assertMissing($image->path);
    }
}
