<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_property_inquiry_without_contacting_the_owner_directly(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-private@example.test',
            'phone' => '+201111111111',
            'whatsapp' => '+201111111111',
        ]);

        $property = Property::factory()->for($owner)->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
            'contact_phone' => '+201222222222',
            'contact_whatsapp' => '+201222222222',
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertDontSee('owner-private@example.test')
            ->assertDontSee('+201111111111')
            ->assertDontSee('+201222222222')
            ->assertSee('properties/'.$property->slug.'/inquiries');

        $response = $this->post(route('properties.inquiries.store', $property), [
            'name' => 'عميل مهتم',
            'phone' => '01012345678',
            'email' => 'customer@example.test',
            'message' => 'أرغب في تحديد موعد لمعاينة العقار.',
            'website' => '',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('property_inquiries', [
            'property_id' => $property->id,
            'submitted_by' => null,
            'name' => 'عميل مهتم',
            'phone' => '01012345678',
            'email' => 'customer@example.test',
            'status' => PropertyInquiry::STATUS_NEW,
        ]);
    }

    public function test_authenticated_customer_is_linked_to_their_inquiry(): void
    {
        $customer = User::factory()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($customer)->post(route('properties.inquiries.store', $property), [
            'name' => $customer->name,
            'phone' => '01012345678',
            'email' => $customer->email,
            'message' => 'أرغب في معرفة تفاصيل إضافية عن العقار.',
            'website' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('property_inquiries', [
            'property_id' => $property->id,
            'submitted_by' => $customer->id,
        ]);
    }

    public function test_inquiries_cannot_be_submitted_for_unpublished_or_future_properties(): void
    {
        foreach ([
            ['status' => PropertyStatus::Pending, 'published_at' => null],
            ['status' => PropertyStatus::Published, 'published_at' => now()->addDay()],
        ] as $attributes) {
            $property = Property::factory()->create($attributes);

            $this->post(route('properties.inquiries.store', $property), [
                'name' => 'عميل مهتم',
                'phone' => '01012345678',
                'message' => 'أرغب في تحديد موعد لمعاينة العقار.',
                'website' => '',
            ])->assertNotFound();
        }

        $this->assertSame(0, PropertyInquiry::query()->count());
    }

    public function test_invalid_or_bot_filled_inquiry_is_rejected(): void
    {
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->from(route('properties.show', $property))
            ->post(route('properties.inquiries.store', $property), [
                'name' => 'A',
                'phone' => 'invalid',
                'message' => 'short',
                'website' => 'spam.example',
            ])
            ->assertRedirect(route('properties.show', $property))
            ->assertSessionHasErrors(['name', 'phone', 'message', 'website']);

        $this->assertSame(0, PropertyInquiry::query()->count());
    }
}
