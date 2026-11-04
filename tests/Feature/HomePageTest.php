<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_arabic_rtl_layout(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('الإمداد العقارية')
            ->assertSee('ابحث عن بيتك القادم');
    }

    public function test_home_page_shows_featured_published_properties_only(): void
    {
        $featured = Property::factory()->featured()->create([
            'title' => 'عقار مميز للاختبار',
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $regular = Property::factory()->create([
            'title' => 'عقار عادي غير مميز',
            'is_featured' => false,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $pending = Property::factory()->featured()->create([
            'title' => 'عقار مميز بانتظار المراجعة',
            'status' => PropertyStatus::Pending,
            'published_at' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($featured->title)
            ->assertDontSee($regular->title)
            ->assertDontSee($pending->title);
    }

    public function test_home_page_search_form_points_to_property_listing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('action="'.route('properties.index').'"', false)
            ->assertSee('name="governorate"', false)
            ->assertSee('name="purpose"', false)
            ->assertSee('name="type"', false);
    }
}
