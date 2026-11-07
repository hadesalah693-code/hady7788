<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyStatus;
use App\Livewire\Properties\CreateProperty;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('account.properties.create'))->assertRedirect(route('login'));
    }

    public function test_suspended_user_cannot_access_create_page(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)
            ->get(route('account.properties.create'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_save_a_draft_with_minimal_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $governorate = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $type = PropertyType::factory()->create();

        Livewire::test(CreateProperty::class)
            ->set('title', 'مسودة عقار تجريبي')
            ->set('governorate_id', $governorate->id)
            ->set('property_type_id', $type->id)
            ->set('price', 1000000)
            ->set('area', 100)
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', 'تم حفظ المسودة بنجاح.');

        $property = Property::where('title', 'مسودة عقار تجريبي')->first();

        $this->assertNotNull($property);
        $this->assertSame($user->id, $property->user_id);
        $this->assertSame(PropertyStatus::Draft, $property->status);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->published_at);
        $this->assertNull($property->published_by);
        $this->assertSame(0, $property->views_count);
    }

    public function test_title_is_required_for_draft(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateProperty::class)
            ->set('title', '')
            ->call('saveDraft')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_verified_user_can_submit_property_for_review(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $type = PropertyType::factory()->create();

        $governorate = Governorate::factory()->create(['name_ar' => 'القاهرة']);

        $this->actingAs($user);

        Livewire::test(CreateProperty::class)
            ->set('title', 'شقة للبيع في القاهرة')
            ->set('description', 'وصف تفصيلي للعقار يحتوي على معلومات كافية.')
            ->set('property_type_id', $type->id)
            ->set('governorate_id', $governorate->id)
            ->set('price', 1500000)
            ->set('area', 120)
            ->call('submitForReview')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.');

        $property = Property::where('title', 'شقة للبيع في القاهرة')->first();

        $this->assertNotNull($property);
        $this->assertSame($user->id, $property->user_id);
        $this->assertSame(PropertyStatus::Pending, $property->status);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->published_at);
        $this->assertNull($property->published_by);
    }

    public function test_unverified_user_cannot_submit_for_review(): void
    {
        $user = User::factory()->broker()->create();

        $this->actingAs($user);

        Livewire::test(CreateProperty::class)
            ->set('title', 'عقار تجريبي')
            ->set('description', 'وصف تفصيلي للعقار.')
            ->call('submitForReview')
            ->assertForbidden();

        $this->assertDatabaseMissing('properties', ['title' => 'عقار تجريبي']);
    }

    public function test_regular_user_cannot_submit_for_review(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateProperty::class)
            ->set('title', 'عقار تجريبي')
            ->set('description', 'وصف تفصيلي للعقار.')
            ->call('submitForReview')
            ->assertForbidden();

        $this->assertDatabaseMissing('properties', ['title' => 'عقار تجريبي']);
    }

    public function test_submit_requires_complete_data(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $this->actingAs($user);

        Livewire::test(CreateProperty::class)
            ->set('title', 'عقار ناقص')
            ->call('submitForReview')
            ->assertHasErrors(['description', 'property_type_id', 'governorate_id', 'price', 'area']);
    }

    public function test_user_cannot_set_administrative_fields(): void
    {
        // The component exposes no public properties for admin fields.
        $reflection = new \ReflectionClass(CreateProperty::class);

        foreach (['user_id', 'status', 'is_featured', 'published_at', 'published_by', 'views_count'] as $field) {
            $this->assertFalse($reflection->hasProperty($field), "Property {$field} must not be settable by user.");
        }
    }
}
