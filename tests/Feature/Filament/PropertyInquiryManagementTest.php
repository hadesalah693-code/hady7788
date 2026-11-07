<?php

namespace Tests\Feature\Filament;

use App\Enums\PropertyStatus;
use App\Filament\Resources\PropertyInquiries\Pages\ListPropertyInquiries;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyInquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_property_inquiries_management_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/property-inquiries')
            ->assertOk()
            ->assertSee('استفسارات العقارات');
    }

    public function test_non_admin_cannot_access_property_inquiries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/property-inquiries')
            ->assertForbidden();
    }

    public function test_admin_can_update_inquiry_follow_up_status_and_internal_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);
        $inquiry = PropertyInquiry::query()->create([
            'property_id' => $property->id,
            'name' => 'عميل',
            'phone' => '01012345678',
            'message' => 'أرغب في معاينة العقار.',
            'status' => PropertyInquiry::STATUS_NEW,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPropertyInquiries::class)
            ->callTableAction('edit', $inquiry, [
                'status' => PropertyInquiry::STATUS_IN_PROGRESS,
                'internal_notes' => 'تم التواصل مع العميل وتحديد موعد.',
            ])
            ->assertHasNoErrors();

        $inquiry->refresh();

        $this->assertSame(PropertyInquiry::STATUS_IN_PROGRESS, $inquiry->status);
        $this->assertSame('تم التواصل مع العميل وتحديد موعد.', $inquiry->internal_notes);
    }
}
