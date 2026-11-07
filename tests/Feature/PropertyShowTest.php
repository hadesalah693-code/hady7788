<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_details_page_shows_published_property(): void
    {
        $property = Property::factory()->create([
            'title' => 'فيلا مستقلة للاختبار',
            'slug' => 'test-villa',
            'price' => 3_200_000,
            'area' => 145,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
            'views_count' => 12,
        ]);

        PropertyImage::factory()->for($property)->cover()->create([
            'path' => 'images/demo/villa-1.svg',
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee($property->title)
            ->assertSee('3,200,000')
            ->assertSee('145 م²');
    }

    public function test_public_property_page_never_exposes_the_exact_address(): void
    {
        $property = Property::factory()->create([
            'address' => 'PRIVATE-STREET-ADDRESS-98765',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertDontSee('PRIVATE-STREET-ADDRESS-98765')
            ->assertDontSee('streetAddress');
    }

    public function test_property_details_page_increments_views_count(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
            'views_count' => 5,
        ]);

        $this->get(route('properties.show', $property))->assertOk();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'views_count' => 6,
        ]);
    }

    public function test_property_details_page_returns_not_found_for_unpublished_property(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatus::Pending,
            'published_at' => null,
        ]);

        $this->get(route('properties.show', $property))->assertNotFound();
    }

    public function test_property_details_page_returns_not_found_for_unknown_slug(): void
    {
        $this->get('/properties/does-not-exist')->assertNotFound();
    }

    public function test_property_details_page_shows_similar_properties(): void
    {
        $property = Property::factory()->create([
            'title' => 'العقار الأساسي',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $similar = Property::factory()->create([
            'title' => 'عقار مشابه في نفس النوع',
            'property_type_id' => $property->property_type_id,
            'governorate_id' => $property->governorate_id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee($similar->title);
    }
}
