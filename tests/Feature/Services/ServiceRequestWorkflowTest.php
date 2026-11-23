<?php

namespace Tests\Feature\Services;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\ServiceRequestStatus;
use App\Livewire\Services\ProviderRequests;
use App\Models\CommissionSetting;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceRequestWorkflow;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function workflow(): ServiceRequestWorkflow
    {
        return app(ServiceRequestWorkflow::class);
    }

    private function providerFor(?User $user = null): ServiceProvider
    {
        return ServiceProvider::factory()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
        ]);
    }

    public function test_only_an_admin_can_assign_a_request(): void
    {
        $request = ServiceRequest::factory()->create();
        $provider = $this->providerFor();
        $customer = User::factory()->create();

        $this->expectException(AuthorizationException::class);

        $this->workflow()->assign($request, $provider, $customer);
    }

    public function test_admin_can_assign_an_open_request_to_an_available_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = $this->providerFor();
        $request = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::New]);

        $result = $this->workflow()->assign($request, $provider, $admin);

        $this->assertSame(ServiceRequestStatus::Assigned, $result->status);
        $this->assertSame($provider->id, $result->service_provider_id);
        $this->assertNotNull($result->assigned_at);
    }

    public function test_admin_cannot_assign_an_unavailable_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->unavailable()->create();
        $request = ServiceRequest::factory()->create();

        $this->expectException(ValidationException::class);

        $this->workflow()->assign($request, $provider, $admin);
    }

    public function test_admin_cannot_assign_a_completed_request(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = $this->providerFor();
        $request = ServiceRequest::factory()->completed()->create();

        $this->expectException(ValidationException::class);

        $this->workflow()->assign($request, $provider, $admin);
    }

    public function test_assigned_provider_can_accept_the_request(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $result = $this->workflow()->accept($request, $providerUser);

        $this->assertSame(ServiceRequestStatus::Accepted, $result->status);
        $this->assertNotNull($result->accepted_at);
    }

    public function test_a_provider_who_is_not_assigned_cannot_accept_the_request(): void
    {
        $provider = $this->providerFor();
        $otherUser = User::factory()->create();
        $this->providerFor($otherUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->expectException(AuthorizationException::class);

        $this->workflow()->accept($request, $otherUser);
    }

    public function test_provider_rejection_returns_the_request_to_the_admin_queue(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $result = $this->workflow()->reject($request, $providerUser, 'لا أغطي هذه المنطقة');

        $this->assertSame(ServiceRequestStatus::New, $result->status);
        $this->assertNull($result->service_provider_id);
        $this->assertNull($result->assigned_at);
        $this->assertStringContainsString('لا أغطي هذه المنطقة', (string) $result->admin_notes);
    }

    public function test_provider_can_start_and_complete_the_request(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->accepted()->create([
            'service_provider_id' => $provider->id,
        ]);

        $started = $this->workflow()->start($request, $providerUser);

        $this->assertSame(ServiceRequestStatus::InProgress, $started->status);
        $this->assertNotNull($started->started_at);

        $completed = $this->workflow()->complete($started, $providerUser);

        $this->assertSame(ServiceRequestStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertSame(1, $provider->refresh()->completed_requests_count);
    }

    public function test_request_cannot_be_completed_without_being_started(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->expectException(ValidationException::class);

        $this->workflow()->complete($request, $providerUser);
    }

    public function test_customer_can_cancel_an_open_request(): void
    {
        $customer = User::factory()->create();
        $request = ServiceRequest::factory()->create([
            'user_id' => $customer->id,
            'status' => ServiceRequestStatus::New,
        ]);

        $result = $this->workflow()->cancel($request, $customer, 'لم أعد بحاجة للخدمة');

        $this->assertSame(ServiceRequestStatus::Cancelled, $result->status);
        $this->assertNotNull($result->cancelled_at);
        $this->assertSame('لم أعد بحاجة للخدمة', $result->cancellation_reason);
    }

    public function test_completed_request_cannot_be_cancelled(): void
    {
        $customer = User::factory()->create();
        $request = ServiceRequest::factory()->completed()->create(['user_id' => $customer->id]);

        $this->expectException(ValidationException::class);

        $this->workflow()->cancel($request, $customer);
    }

    public function test_another_customer_cannot_cancel_someone_elses_request(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $request = ServiceRequest::factory()->create(['user_id' => $owner->id]);

        $this->expectException(AuthorizationException::class);

        $this->workflow()->cancel($request, $other);
    }

    public function test_completing_a_request_creates_the_commission(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 50,
            'is_active' => true,
        ]);

        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->inProgress()->create([
            'service_provider_id' => $provider->id,
            'quoted_price' => 400,
        ]);

        $this->workflow()->complete($request, $providerUser);

        $commission = $request->refresh()->commission;

        $this->assertNotNull($commission);
        $this->assertSame(CommissionStatus::Pending, $commission->status);
        $this->assertEquals(50, (float) $commission->amount);
    }

    public function test_provider_requests_page_lists_only_the_providers_own_requests(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);

        $ownRequest = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
            'title' => 'طلب خاص بي',
        ]);

        ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $this->providerFor()->id,
            'title' => 'طلب مقدم خدمة آخر',
        ]);

        $this->actingAs($providerUser);

        Livewire::test(ProviderRequests::class)
            ->assertSee('طلب خاص بي')
            ->assertDontSee('طلب مقدم خدمة آخر');
    }

    public function test_provider_can_accept_a_request_from_the_dashboard(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->actingAs($providerUser);

        Livewire::test(ProviderRequests::class)
            ->call('accept', $request->id)
            ->assertHasNoErrors()
            ->assertSet('actionMessage', 'تم قبول الطلب.');

        $this->assertSame(ServiceRequestStatus::Accepted, $request->refresh()->status);
    }

    public function test_provider_can_reject_a_request_from_the_dashboard(): void
    {
        $providerUser = User::factory()->create();
        $provider = $this->providerFor($providerUser);
        $request = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->actingAs($providerUser);

        Livewire::test(ProviderRequests::class)
            ->set('rejectReason', 'المسافة بعيدة')
            ->call('reject', $request->id)
            ->assertHasNoErrors();

        $this->assertSame(ServiceRequestStatus::New, $request->refresh()->status);
        $this->assertNull($request->service_provider_id);
    }

    public function test_user_without_a_provider_profile_cannot_open_the_provider_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('account.service-provider.requests'))->assertForbidden();
    }
}
