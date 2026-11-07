<?php

namespace Tests\Feature;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_page_shows_only_published_properties(): void
    {
        $published = Property::factory()->create([
            'title' => 'TOKEN-PUBLISHED-LISTING',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $pending = Property::factory()->create([
            'title' => 'TOKEN-PENDING-LISTING',
            'status' => PropertyStatus::Pending,
            'published_at' => null,
        ]);

        $this->get(route('properties.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($pending->title);
    }

    public function test_listing_page_filters_properties_by_purpose(): void
    {
        $sale = Property::factory()->forSale()->create([
            'title' => 'TOKEN-SALE-LISTING',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $rent = Property::factory()->forRent()->create([
            'title' => 'TOKEN-RENT-LISTING',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.index', ['purpose' => PropertyPurpose::Sale->value]))
            ->assertOk()
            ->assertSee($sale->title)
            ->assertDontSee($rent->title);
    }

    public function test_listing_page_filters_properties_by_governorate(): void
    {
        $cairo = Governorate::factory()->create(['name_ar' => 'القاهرة', 'slug' => 'cairo-test']);
        $giza = Governorate::factory()->create(['name_ar' => 'الجيزة', 'slug' => 'giza-test']);

        $cairoProperty = Property::factory()->create([
            'title' => 'TOKEN-CAIRO-LISTING',
            'governorate_id' => $cairo->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $gizaProperty = Property::factory()->create([
            'title' => 'TOKEN-GIZA-LISTING',
            'governorate_id' => $giza->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.index', ['governorate' => 'cairo-test']))
            ->assertOk()
            ->assertSee($cairoProperty->title)
            ->assertDontSee($gizaProperty->title);
    }

    public function test_listing_page_searches_properties_by_title(): void
    {
        $match = Property::factory()->create([
            'title' => 'شقة بإطلالة على النيل في الزمالك',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $other = Property::factory()->create([
            'title' => 'TOKEN-OTHER-SEARCH',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.index', ['q' => 'الزمالك']))
            ->assertOk()
            ->assertSee($match->title)
            ->assertDontSee($other->title);
    }

    public function test_listing_search_does_not_match_a_private_exact_address(): void
    {
        $property = Property::factory()->create([
            'title' => 'TOKEN-PRIVATE-ADDRESS-LISTING',
            'address' => 'PRIVATE-ADDRESS-TOKEN-12345',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.index', ['q' => 'PRIVATE-ADDRESS-TOKEN-12345']))
            ->assertOk()
            ->assertDontSee($property->title);
    }

    public function test_listing_page_filters_properties_by_type(): void
    {
        $apartment = PropertyType::factory()->create(['name_ar' => 'شقة', 'slug' => 'apartment-test']);
        $villa = PropertyType::factory()->create(['name_ar' => 'فيلا', 'slug' => 'villa-test']);

        $apartmentProperty = Property::factory()->create([
            'title' => 'TOKEN-APARTMENT-LISTING',
            'property_type_id' => $apartment->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $villaProperty = Property::factory()->create([
            'title' => 'TOKEN-VILLA-LISTING',
            'property_type_id' => $villa->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('properties.index', ['type' => 'apartment-test']))
            ->assertOk()
            ->assertSee($apartmentProperty->title)
            ->assertDontSee($villaProperty->title);
    }

    public function test_listing_page_paginates_results(): void
    {
        foreach (range(1, 10) as $index) {
            Property::factory()->create([
                'title' => 'TOKEN-PAGE-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => PropertyStatus::Published,
                'published_at' => now()->subDays($index),
            ]);
        }

        $this->get(route('properties.index'))
            ->assertOk()
            ->assertSee('TOKEN-PAGE-01')
            ->assertDontSee('TOKEN-PAGE-10');

        $this->get(route('properties.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('TOKEN-PAGE-10')
            ->assertDontSee('TOKEN-PAGE-01');
    }

    public function test_listing_page_shows_empty_state_when_no_results(): void
    {
        $this->get(route('properties.index', ['q' => 'لا-يوجد-عقار-بهذا-الاسم']))
            ->assertOk()
            ->assertSee('لا توجد نتائج مطابقة');
    }
}
