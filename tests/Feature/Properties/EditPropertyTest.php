<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyStatus;
use App\Livewire\Properties\EditProperty;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditPropertyTest extends TestCase
{
    use RefreshDatabase;

    private function makeProperty(User $user, array $attributes = []): Property
    {
        return Property::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => PropertyStatus::Draft,
            'description' => 'وصف تفصيلي كافٍ للعقار يتجاوز الحد الأدنى.',
            'price' => 1000000,
            'area' => 120,
            'views_count' => 0,
            'is_featured' => false,
            'published_at' => null,
            'published_by' => null,
        ], $attributes));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $property = Property::factory()->create();

        $this->get(route('account.properties.edit', $property->id))
            ->assertRedirect(route('login'));
    }

    public function test_suspended_user_is_signed_out(): void
    {
        $user = User::factory()->suspended()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user)
            ->get(route('account.properties.edit', $property->id))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_owner_can_open_the_edit_page(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user)
            ->get(route('account.properties.edit', $property->id))
            ->assertOk()
            ->assertSee('تعديل العقار')
            ->assertSee($property->title);
    }

    public function test_non_owner_cannot_open_the_edit_page(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty($owner);

        $this->actingAs($other)
            ->get(route('account.properties.edit', $property->id))
            ->assertForbidden();
    }

    public function test_idor_attempt_is_rejected_by_the_policy(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty($owner);

        $this->actingAs($other);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->assertForbidden();
    }

    public function test_public_description_cannot_repeat_the_private_exact_address(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'address' => 'PRIVATE-ADDRESS-UNIT-9988',
        ]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('description', 'العقار يقع في PRIVATE-ADDRESS-UNIT-9988')
            ->call('save')
            ->assertHasErrors(['description']);

        $this->assertSame(
            'وصف تفصيلي كافٍ للعقار يتجاوز الحد الأدنى.',
            $property->refresh()->description,
        );
    }

    public function test_owner_can_update_editable_fields_without_touching_admin_fields(): void
    {
        $user = User::factory()->create();

        $property = $this->makeProperty($user, [
            'views_count' => 7,
            'is_featured' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('title', 'عنوان محدث للعقار')
            ->set('price', 2000000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', 'تم حفظ التعديلات بنجاح.');

        $property->refresh();

        $this->assertSame('عنوان محدث للعقار', $property->title);
        $this->assertSame('2000000.00', $property->price);
        $this->assertSame(PropertyStatus::Draft, $property->status);
        $this->assertSame($user->id, $property->user_id);
        $this->assertSame(7, $property->views_count);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->published_at);
        $this->assertNull($property->published_by);
    }

    public function test_substantive_edit_to_published_property_returns_it_to_review(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $admin = User::factory()->admin()->create();

        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDays(3),
            'published_by' => $admin->id,
            'is_featured' => true,
            'views_count' => 42,
        ]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('title', 'عنوان معدل جوهريًا')
            ->call('save')
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Pending, $property->status);
        $this->assertSame('عنوان معدل جوهريًا', $property->title);
        $this->assertSame($user->id, $property->user_id);
        $this->assertNotNull($property->published_at);
        $this->assertSame($admin->id, $property->published_by);
        $this->assertTrue($property->is_featured);
        $this->assertSame(42, $property->views_count);
    }

    public function test_contact_only_edit_to_published_property_keeps_it_published(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('contact_phone', '+201000000000')
            ->call('save')
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Published, $property->status);
        $this->assertSame('+201000000000', $property->contact_phone);
    }

    public function test_substantive_edit_requires_complete_data_before_returning_to_review(): void
    {
        $user = User::factory()->verifiedBroker()->create();

        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
            'title' => 'العنوان الأصلي المنشور',
        ]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('title', 'عنوان جديد غير مكتمل')
            ->set('description', '')
            ->call('save')
            ->assertHasErrors(['description']);

        $property->refresh();

        $this->assertSame(PropertyStatus::Published, $property->status);
        $this->assertSame('العنوان الأصلي المنشور', $property->title);
    }

    public function test_verified_owner_can_submit_a_draft_for_review(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->call('submitForReview')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.');

        $this->assertSame(PropertyStatus::Pending, $property->refresh()->status);
    }

    public function test_regular_user_cannot_submit_a_draft_for_review(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->call('submitForReview')
            ->assertForbidden();

        $this->assertSame(PropertyStatus::Draft, $property->refresh()->status);
    }

    public function test_pending_property_cannot_be_submitted_again(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $property = $this->makeProperty($user, ['status' => PropertyStatus::Pending]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->call('submitForReview')
            ->assertSet('errorMessage', 'لا يمكن إرسال هذا العقار للمراجعة في حالته الحالية.');

        $this->assertSame(PropertyStatus::Pending, $property->refresh()->status);
    }

    public function test_editing_an_archived_property_keeps_it_archived(): void
    {
        $user = User::factory()->verifiedBroker()->create();
        $property = $this->makeProperty($user, ['status' => PropertyStatus::Archived]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('title', 'عنوان محدث لعقار مؤرشف')
            ->call('save')
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Archived, $property->status);
        $this->assertSame('عنوان محدث لعقار مؤرشف', $property->title);
    }

    public function test_city_must_belong_to_the_selected_governorate(): void
    {
        $user = User::factory()->create();

        $governorate = Governorate::factory()->create();
        $otherGovernorate = Governorate::factory()->create();
        $foreignCity = City::factory()->create(['governorate_id' => $otherGovernorate->id]);

        $property = $this->makeProperty($user, ['governorate_id' => $governorate->id]);

        $this->actingAs($user);

        Livewire::test(EditProperty::class, ['property' => $property->id])
            ->set('governorate_id', $governorate->id)
            ->set('city_id', $foreignCity->id)
            ->call('save')
            ->assertHasErrors(['city_id']);
    }

    public function test_administrative_fields_cannot_be_injected_from_the_payload(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        $injected = [
            'user_id' => $other->id,
            'status' => PropertyStatus::Published->value,
            'is_featured' => true,
            'published_at' => now()->toDateTimeString(),
            'published_by' => $other->id,
            'views_count' => 9999,
        ];

        foreach ($injected as $field => $value) {
            $blocked = false;

            try {
                Livewire::test(EditProperty::class, ['property' => $property->id])
                    ->set($field, $value);
            } catch (\Throwable) {
                // Expected: the property is not exposed on the component.
                $blocked = true;
            }

            $this->assertTrue($blocked, "The {$field} field must not be settable from the component payload.");
        }

        $property->refresh();

        $this->assertSame($user->id, $property->user_id);
        $this->assertSame(PropertyStatus::Draft, $property->status);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->published_at);
        $this->assertNull($property->published_by);
        $this->assertSame(0, $property->views_count);
    }
}
