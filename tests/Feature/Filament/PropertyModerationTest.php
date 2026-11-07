<?php

namespace Tests\Feature\Filament;

use App\Enums\PropertyStatus;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_properties_management_table(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/properties')
            ->assertOk()
            ->assertSee('العقارات');
    }

    public function test_admin_can_approve_a_pending_property(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Pending,
            'published_at' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListProperties::class)
            ->callTableAction('approve', $property)
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Published, $property->status);
        $this->assertNotNull($property->published_at);
        $this->assertSame($admin->id, $property->published_by);
    }

    public function test_admin_can_reject_a_pending_property_with_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Pending,
            'published_at' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListProperties::class)
            ->callTableAction('reject', $property, [
                'rejection_reason' => 'بيانات العقار غير مكتملة.',
            ])
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Rejected, $property->status);
        $this->assertSame('بيانات العقار غير مكتملة.', $property->rejection_reason);
    }

    public function test_admin_can_archive_a_property_with_an_optional_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin);

        Livewire::test(ListProperties::class)
            ->callTableAction('archive', $property, [
                'archive_reason' => 'أُزيل الإعلان بطلب المالك.',
            ])
            ->assertHasNoErrors();

        $property->refresh();

        $this->assertSame(PropertyStatus::Archived, $property->status);
        $this->assertSame('أُزيل الإعلان بطلب المالك.', $property->archive_reason);
    }
}
