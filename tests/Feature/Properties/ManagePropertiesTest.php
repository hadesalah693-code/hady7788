<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Livewire\Properties\EditProperty;
use App\Livewire\Properties\MyProperties;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagePropertiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('account.properties.index'))->assertRedirect(route('login'));
    }

    public function test_suspended_user_is_signed_out(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)
            ->get(route('account.properties.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_only_sees_their_own_properties(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Property::factory()->create(['user_id' => $user->id, 'title' => 'عقار المستخدم الحالي']);
        Property::factory()->create(['user_id' => $other->id, 'title' => 'عقار مستخدم آخر']);

        $this->actingAs($user)
            ->get(route('account.properties.index'))
            ->assertOk()
            ->assertSee('عقار المستخدم الحالي')
            ->assertDontSee('عقار مستخدم آخر');
    }

    public function test_search_and_filters_restrict_results(): void
    {
        $user = User::factory()->create();

        $apartment = PropertyType::factory()->create(['name_ar' => 'شقة', 'slug' => 'apartment']);
        $villa = PropertyType::factory()->create(['name_ar' => 'فيلا', 'slug' => 'villa']);

        Property::factory()->create([
            'user_id' => $user->id,
            'title' => 'شقة مميزة للبحث',
            'status' => PropertyStatus::Draft,
            'purpose' => PropertyPurpose::Sale,
            'property_type_id' => $apartment->id,
        ]);

        Property::factory()->create([
            'user_id' => $user->id,
            'title' => 'فيلا أخرى',
            'status' => PropertyStatus::Published,
            'purpose' => PropertyPurpose::Rent,
            'property_type_id' => $villa->id,
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->set('search', 'مميزة')
            ->assertSee('شقة مميزة للبحث')
            ->assertDontSee('فيلا أخرى');

        Livewire::test(MyProperties::class)
            ->set('statusFilter', PropertyStatus::Draft->value)
            ->assertSee('شقة مميزة للبحث')
            ->assertDontSee('فيلا أخرى');

        Livewire::test(MyProperties::class)
            ->set('purpose', PropertyPurpose::Rent->value)
            ->assertSee('فيلا أخرى')
            ->assertDontSee('شقة مميزة للبحث');

        Livewire::test(MyProperties::class)
            ->set('type', 'apartment')
            ->assertSee('شقة مميزة للبحث')
            ->assertDontSee('فيلا أخرى');
    }

    public function test_duplicate_creates_a_fresh_draft_without_admin_or_related_data(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $publisher = User::factory()->create();

        $original = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Published,
            'is_featured' => true,
            'published_at' => now()->subDays(2),
            'published_by' => $publisher->id,
            'views_count' => 321,
            'title' => 'عقار قابل للنسخ',
            'latitude' => 30.0444196,
            'longitude' => 31.2357116,
        ]);

        PropertyImage::factory()->cover()->create(['property_id' => $original->id]);

        $amenity = Amenity::create(['name_ar' => 'مسبح', 'slug' => 'pool', 'is_active' => true]);
        $original->amenities()->attach($amenity->id);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('duplicate', $original->id)
            ->assertSet('actionMessage', 'تم نسخ العقار كمسودة جديدة.');

        $copy = Property::query()->whereKeyNot($original->id)->firstOrFail();

        $this->assertSame($user->id, $copy->user_id);
        $this->assertSame('عقار قابل للنسخ', $copy->title);
        $this->assertSame(PropertyStatus::Draft, $copy->status);
        $this->assertNotSame($original->slug, $copy->slug);
        $this->assertFalse($copy->is_featured);
        $this->assertNull($copy->published_at);
        $this->assertNull($copy->published_by);
        $this->assertSame(0, $copy->views_count);
        $this->assertNull($copy->latitude);
        $this->assertNull($copy->longitude);
        $this->assertSame(0, $copy->images()->count());
        $this->assertSame(0, $copy->amenities()->count());
    }

    public function test_user_cannot_duplicate_another_users_property(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other);

        Livewire::test(MyProperties::class)
            ->call('duplicate', $property->id)
            ->assertForbidden();

        $this->assertSame(1, Property::query()->count());
    }

    public function test_owner_can_archive_and_reactivation_returns_to_review(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $property = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
            'description' => 'وصف كامل وكافٍ للعقار قبل الأرشفة.',
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('archive', $property->id)
            ->assertSet('actionMessage', 'تم أرشفة العقار.');

        $this->assertSame(PropertyStatus::Archived, $property->refresh()->status);

        Livewire::test(MyProperties::class)
            ->call('reactivate', $property->id)
            ->assertSet('actionMessage', 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.');

        $this->assertSame(PropertyStatus::Pending, $property->refresh()->status);
    }

    public function test_unverified_owner_cannot_reactivate_archived_property(): void
    {
        $user = User::factory()->broker()->create();

        $property = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Archived,
            'description' => 'وصف كامل وكافٍ للعقار.',
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('reactivate', $property->id)
            ->assertSet('actionError', 'حسابك غير موثق. أكمل توثيق حسابك قبل إعادة تفعيل العقار.');

        $this->assertSame(PropertyStatus::Archived, $property->refresh()->status);
    }

    public function test_incomplete_archived_property_cannot_be_reactivated(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $property = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Archived,
            'description' => '',
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('reactivate', $property->id)
            ->assertSet('actionError', 'أكمل بيانات العقار المطلوبة قبل إعادة تفعيله وإرساله للمراجعة.');

        $this->assertSame(PropertyStatus::Archived, $property->refresh()->status);
    }

    public function test_published_property_can_be_marked_sold_or_rented(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $sale = Property::factory()->forSale()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Published,
        ]);

        $rent = Property::factory()->forRent()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Published,
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)->call('markSold', $sale->id);
        $this->assertSame(PropertyStatus::Sold, $sale->refresh()->status);

        Livewire::test(MyProperties::class)->call('markRented', $rent->id);
        $this->assertSame(PropertyStatus::Rented, $rent->refresh()->status);
    }

    public function test_non_published_property_cannot_be_marked_sold(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $draft = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Draft,
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('markSold', $draft->id)
            ->assertSet('actionError', 'لا يمكن تغيير حالة العقار إلا إذا كان منشورًا.');

        $this->assertSame(PropertyStatus::Draft, $draft->refresh()->status);
    }

    public function test_sold_property_cannot_be_marked_rented(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $sold = Property::factory()->create([
            'user_id' => $user->id,
            'status' => PropertyStatus::Sold,
        ]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('markRented', $sold->id)
            ->assertSet('actionError', 'لا يمكن تغيير حالة العقار إلا إذا كان منشورًا.');

        $this->assertSame(PropertyStatus::Sold, $sold->refresh()->status);
    }

    public function test_owner_soft_deletes_their_property(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(MyProperties::class)
            ->call('delete', $property->id)
            ->assertSet('actionMessage', 'تم حذف العقار.');

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
        $this->assertNotNull(Property::withTrashed()->find($property->id));
    }

    public function test_user_cannot_delete_another_users_property(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other);

        Livewire::test(MyProperties::class)
            ->call('delete', $property->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_management_components_do_not_expose_administrative_fields(): void
    {
        $administrative = ['user_id', 'status', 'is_featured', 'published_at', 'published_by', 'views_count'];

        foreach ([MyProperties::class, EditProperty::class] as $component) {
            $reflection = new \ReflectionClass($component);

            foreach ($administrative as $field) {
                $this->assertFalse(
                    $reflection->hasProperty($field),
                    "Component {$component} must not expose the {$field} property."
                );
            }
        }
    }
}
