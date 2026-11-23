<?php

namespace Tests\Feature\Services;

use App\Enums\CommissionStatus;
use App\Enums\ServiceProviderStatus;
use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceCategories\Pages\CreateServiceCategory;
use App\Filament\Resources\ServiceCommissions\Pages\ListServiceCommissions;
use App\Filament\Resources\ServiceProviders\Pages\ListServiceProviders;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\ServiceReviews\Pages\ListServiceReviews;
use App\Models\ServiceCategory;
use App\Models\ServiceCommission;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_service_management_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            '/admin/service-categories',
            '/admin/service-providers',
            '/admin/service-requests',
            '/admin/service-reviews',
            '/admin/service-commissions',
            '/admin/commission-settings',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_non_admin_cannot_open_the_service_management_pages(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/admin/service-categories',
            '/admin/service-providers',
            '/admin/service-requests',
            '/admin/service-reviews',
            '/admin/service-commissions',
            '/admin/commission-settings',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_create_a_service_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(CreateServiceCategory::class)
            ->fillForm([
                'name_ar' => 'صيانة منزلية',
                'name_en' => 'Home Maintenance',
                'slug' => 'home-maintenance',
                'description' => 'خدمات الصيانة المنزلية العامة.',
                'icon' => 'heroicon-o-wrench',
                'sort_order' => 3,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_categories', [
            'name_ar' => 'صيانة منزلية',
            'slug' => 'home-maintenance',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_approve_a_pending_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->pending()->create();

        $this->actingAs($admin);

        Livewire::test(ListServiceProviders::class)
            ->callTableAction('approve', $provider)
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(ServiceProviderStatus::Active, $provider->status);
        $this->assertTrue($provider->is_available);
        $this->assertNotNull($provider->approved_at);
        $this->assertSame($admin->id, $provider->approved_by);
    }

    public function test_admin_can_reject_a_provider_with_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->pending()->create();

        $this->actingAs($admin);

        Livewire::test(ListServiceProviders::class)
            ->callTableAction('reject', $provider, ['reason' => 'مستندات غير مكتملة'])
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(ServiceProviderStatus::Rejected, $provider->status);
        $this->assertFalse($provider->is_available);
        $this->assertSame('مستندات غير مكتملة', $provider->rejection_reason);
    }

    public function test_admin_can_suspend_an_active_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->create(['status' => ServiceProviderStatus::Active]);

        $this->actingAs($admin);

        Livewire::test(ListServiceProviders::class)
            ->callTableAction('suspend', $provider)
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(ServiceProviderStatus::Suspended, $provider->status);
        $this->assertFalse($provider->is_available);
    }

    public function test_admin_can_assign_a_request_to_a_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ServiceCategory::factory()->create();
        $provider = ServiceProvider::factory()->create();
        $provider->categories()->attach($category);

        $request = ServiceRequest::factory()->create([
            'service_category_id' => $category->id,
            'status' => ServiceRequestStatus::New,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('assign', $request, ['provider_id' => $provider->id])
            ->assertHasNoErrors();

        $request->refresh();

        $this->assertSame(ServiceRequestStatus::Assigned, $request->status);
        $this->assertSame($provider->id, $request->service_provider_id);
        $this->assertNotNull($request->assigned_at);
    }

    public function test_admin_can_advance_a_request_through_execution_statuses(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->create();

        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('accept', $request)
            ->assertHasNoErrors();

        $this->assertSame(ServiceRequestStatus::Accepted, $request->refresh()->status);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('start', $request)
            ->assertHasNoErrors();

        $this->assertSame(ServiceRequestStatus::InProgress, $request->refresh()->status);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('complete', $request)
            ->assertHasNoErrors();

        $this->assertSame(ServiceRequestStatus::Completed, $request->refresh()->status);
        $this->assertNotNull($request->completed_at);
    }

    public function test_admin_can_cancel_a_request_with_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $request = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::New]);

        $this->actingAs($admin);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('cancel', $request, ['reason' => 'طلب مكرر'])
            ->assertHasNoErrors();

        $request->refresh();

        $this->assertSame(ServiceRequestStatus::Cancelled, $request->status);
        $this->assertSame('طلب مكرر', $request->cancellation_reason);
    }

    public function test_admin_can_settle_and_waive_commissions(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = ServiceCommission::factory()->create();
        $otherPending = ServiceCommission::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ListServiceCommissions::class)
            ->callTableAction('settle', $pending)
            ->assertHasNoErrors();

        Livewire::test(ListServiceCommissions::class)
            ->callTableAction('waive', $otherPending)
            ->assertHasNoErrors();

        $this->assertSame(CommissionStatus::Settled, $pending->refresh()->status);
        $this->assertNotNull($pending->settled_at);
        $this->assertSame(CommissionStatus::Waived, $otherPending->refresh()->status);
        $this->assertNull($otherPending->settled_at);
    }

    public function test_admin_can_dismiss_a_reported_review(): void
    {
        $admin = User::factory()->admin()->create();
        $review = ServiceReview::factory()->reported()->create();

        $this->actingAs($admin);

        Livewire::test(ListServiceReviews::class)
            ->callTableAction('dismissReport', $review)
            ->assertHasNoErrors();

        $review->refresh();

        $this->assertFalse($review->is_reported);
        $this->assertNull($review->report_reason);
        $this->assertNull($review->reported_at);
    }
}
