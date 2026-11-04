<?php

namespace Tests\Feature\Properties;

use App\Enums\AmenityCategory;
use App\Enums\PropertyStatus;
use App\Livewire\Properties\PropertyAmenities;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyAmenitiesTest extends TestCase
{
    use RefreshDatabase;

    private function makeProperty(User $user, array $attributes = []): Property
    {
        return Property::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => PropertyStatus::Draft,
            'published_at' => null,
            'published_by' => null,
            'views_count' => 0,
            'is_featured' => false,
        ], $attributes));
    }

    private function amenity(array $attributes = []): Amenity
    {
        return Amenity::factory()->create(array_merge([
            'category' => AmenityCategory::Indoor,
            'is_active' => true,
        ], $attributes));
    }

    public function test_owner_can_select_and_save_multiple_amenities(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $first = $this->amenity(['name_ar' => 'تكييف', 'slug' => 'ac']);
        $second = $this->amenity(['name_ar' => 'أسانسير', 'slug' => 'elevator', 'category' => AmenityCategory::Building]);

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$first->id, $second->id])
            ->call('save')
            ->assertSet('message', 'تم حفظ المرافق بنجاح.')
            ->assertSet('error', null);

        $this->assertEqualsCanonicalizing(
            [$first->id, $second->id],
            $property->amenities()->pluck('amenities.id')->all(),
        );
    }

    public function test_selected_amenities_are_loaded_when_opening_the_editor(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $first = $this->amenity();
        $second = $this->amenity(['slug' => 'second']);

        $property->amenities()->attach([$first->id, $second->id]);

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->assertSet('selected', [(string) $first->id, (string) $second->id]);
    }

    public function test_duplicate_amenity_ids_are_rejected(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);
        $amenity = $this->amenity();

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id, $amenity->id])
            ->call('save')
            ->assertHasErrors('selected.0');

        $this->assertSame(0, $property->amenities()->count());
    }

    public function test_missing_or_inactive_amenities_are_rejected(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);
        $inactive = $this->amenity(['slug' => 'inactive', 'is_active' => false]);

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$inactive->id])
            ->call('save')
            ->assertHasErrors('selected.0');

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [999999])
            ->call('save')
            ->assertHasErrors('selected.0');

        $this->assertSame(0, $property->amenities()->count());
    }

    public function test_saving_removes_amenities_that_are_no_longer_selected(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $keep = $this->amenity(['slug' => 'keep']);
        $remove = $this->amenity(['slug' => 'remove']);

        $property->amenities()->attach([$keep->id, $remove->id]);

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$keep->id])
            ->call('save');

        $this->assertSame([$keep->id], $property->amenities()->pluck('amenities.id')->all());
    }

    public function test_non_owner_cannot_change_amenities(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty($owner);
        $amenity = $this->amenity();

        $this->actingAs($owner);

        $component = Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id]);

        $this->actingAs($other);

        $component->call('save')->assertForbidden();

        $this->assertSame(0, $property->amenities()->count());
    }

    public function test_owner_cannot_change_amenities_of_a_sold_or_rented_property(): void
    {
        $user = User::factory()->create();
        $amenity = $this->amenity();

        foreach ([PropertyStatus::Sold, PropertyStatus::Rented] as $status) {
            $property = $this->makeProperty($user, ['status' => $status]);

            $this->actingAs($user);

            Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
                ->set('selected', [$amenity->id])
                ->call('save')
                ->assertForbidden();
        }
    }

    public function test_admin_can_change_amenities_of_a_sold_property(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $property = $this->makeProperty($owner, ['status' => PropertyStatus::Sold]);
        $amenity = $this->amenity();

        $this->actingAs($admin);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id])
            ->call('save')
            ->assertSet('message', 'تم حفظ المرافق بنجاح.');

        $this->assertSame([$amenity->id], $property->amenities()->pluck('amenities.id')->all());
    }

    public function test_changing_amenities_of_a_published_property_returns_it_to_review(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);
        $amenity = $this->amenity();

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id])
            ->call('save');

        $this->assertSame(PropertyStatus::Pending, $property->refresh()->status);
        $this->assertNotNull($property->published_at);
    }

    public function test_saving_unchanged_amenities_keeps_a_published_property_published(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);
        $amenity = $this->amenity();

        $property->amenities()->attach($amenity->id);

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id])
            ->call('save')
            ->assertSet('message', 'تم حفظ المرافق بنجاح.');

        $this->assertSame(PropertyStatus::Published, $property->refresh()->status);
    }

    public function test_an_archived_property_stays_archived_when_amenities_change(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, ['status' => PropertyStatus::Archived]);
        $amenity = $this->amenity();

        $this->actingAs($user);

        Livewire::test(PropertyAmenities::class, ['propertyId' => $property->id])
            ->set('selected', [$amenity->id])
            ->call('save');

        $this->assertSame(PropertyStatus::Archived, $property->refresh()->status);
    }

    public function test_public_property_page_shows_only_active_amenities_grouped_by_category(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subMinute(),
            'title' => 'عقار بمرافق للاختبار',
        ]);

        $indoor = $this->amenity(['name_ar' => 'تكييف', 'slug' => 'ac-public', 'category' => AmenityCategory::Indoor]);
        $building = $this->amenity(['name_ar' => 'أسانسير', 'slug' => 'elevator-public', 'category' => AmenityCategory::Building]);
        $inactive = $this->amenity(['name_ar' => 'مرفق معطل', 'slug' => 'inactive-public', 'is_active' => false]);

        $property->amenities()->attach([$indoor->id, $building->id, $inactive->id]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('داخل العقار')
            ->assertSee('مرافق المبنى')
            ->assertSee('تكييف')
            ->assertSee('أسانسير')
            ->assertDontSee('مرفق معطل');
    }

    public function test_amenities_are_not_public_for_a_draft_property(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatus::Draft,
            'published_at' => null,
        ]);

        $amenity = $this->amenity(['name_ar' => 'مرفق سري', 'slug' => 'secret']);
        $property->amenities()->attach($amenity->id);

        $this->get(route('properties.show', $property))->assertNotFound();
    }
}
