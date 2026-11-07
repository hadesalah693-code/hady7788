<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyImageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('property-images');
    }

    private function makeProperty(array $attributes = []): Property
    {
        return Property::factory()->create(array_merge([
            'status' => PropertyStatus::Draft,
            'published_at' => null,
            'published_by' => null,
            'views_count' => 0,
            'is_featured' => false,
        ], $attributes));
    }

    private function makeImage(Property $property, array $attributes = []): PropertyImage
    {
        $path = 'properties/'.$property->id.'/'.Str::uuid().'.jpg';

        Storage::disk('property-images')->put($path, 'image-bytes');

        return PropertyImage::factory()->create(array_merge([
            'property_id' => $property->id,
            'disk' => 'property-images',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 11,
            'sort_order' => 0,
            'is_cover' => true,
        ], $attributes));
    }

    public function test_published_property_images_are_publicly_accessible(): void
    {
        $property = $this->makeProperty([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
        $image = $this->makeImage($property);

        $this->get(route('properties.images.show', $image))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_other_authenticated_users_cannot_access_images_before_scheduled_publication(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty([
            'user_id' => $owner->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->addDay(),
        ]);
        $image = $this->makeImage($property);

        $this->actingAs($other)
            ->get(route('properties.images.show', $image))
            ->assertForbidden();
    }

    public function test_guests_cannot_access_images_before_scheduled_publication(): void
    {
        $property = $this->makeProperty([
            'status' => PropertyStatus::Published,
            'published_at' => now()->addDay(),
        ]);
        $image = $this->makeImage($property);

        $this->get(route('properties.images.show', $image))->assertNotFound();
    }

    public function test_guests_cannot_access_images_of_unpublished_properties(): void
    {
        foreach ([
            PropertyStatus::Draft,
            PropertyStatus::Pending,
            PropertyStatus::Rejected,
            PropertyStatus::Archived,
            PropertyStatus::Sold,
            PropertyStatus::Rented,
        ] as $status) {
            $property = $this->makeProperty(['status' => $status]);
            $image = $this->makeImage($property);

            $this->get(route('properties.images.show', $image))->assertNotFound();
        }
    }

    public function test_owner_can_access_images_of_a_private_property(): void
    {
        $owner = User::factory()->create();
        $property = $this->makeProperty([
            'user_id' => $owner->id,
            'status' => PropertyStatus::Draft,
        ]);
        $image = $this->makeImage($property);

        $response = $this->actingAs($owner)
            ->get(route('properties.images.show', $image))
            ->assertOk();

        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_another_user_cannot_access_private_property_images(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty([
            'user_id' => $owner->id,
            'status' => PropertyStatus::Draft,
        ]);
        $image = $this->makeImage($property);

        $this->actingAs($other)
            ->get(route('properties.images.show', $image))
            ->assertForbidden();
    }

    public function test_admin_can_access_private_property_images(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $property = $this->makeProperty([
            'user_id' => $owner->id,
            'status' => PropertyStatus::Rejected,
        ]);
        $image = $this->makeImage($property);

        $this->actingAs($admin)
            ->get(route('properties.images.show', $image))
            ->assertOk();
    }

    public function test_images_of_a_soft_deleted_property_are_not_reachable(): void
    {
        $owner = User::factory()->create();
        $property = $this->makeProperty([
            'user_id' => $owner->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
        $image = $this->makeImage($property);

        $property->delete();

        $this->get(route('properties.images.show', $image))->assertNotFound();

        $this->actingAs($owner)
            ->get(route('properties.images.show', $image))
            ->assertNotFound();
    }

    public function test_legacy_public_demo_images_redirect_to_the_public_asset(): void
    {
        $property = $this->makeProperty([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subMinute(),
        ]);

        $image = PropertyImage::factory()->create([
            'property_id' => $property->id,
            'disk' => 'public',
            'path' => 'images/demo/apartment-1.svg',
            'is_cover' => true,
        ]);

        $this->get(route('properties.images.show', $image))
            ->assertRedirect(asset('images/demo/apartment-1.svg'));
    }

    public function test_private_image_response_does_not_expose_the_storage_path(): void
    {
        $property = $this->makeProperty([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subMinute(),
            'slug' => 'safe-property',
        ]);
        $image = $this->makeImage($property);

        $response = $this->get(route('properties.images.show', $image))->assertOk();

        $disposition = $response->headers->get('Content-Disposition', '');

        $this->assertStringContainsString('safe-property-'.$image->id.'.jpg', $disposition);
        $this->assertStringNotContainsString('properties/', $disposition);
    }
}
