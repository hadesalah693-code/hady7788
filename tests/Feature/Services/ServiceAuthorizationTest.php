<?php

namespace Tests\Feature\Services;

use App\Enums\ServiceProviderStatus;
use App\Enums\ServiceRequestStatus;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ServiceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_their_own_request_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $serviceRequest));
        $this->assertFalse(Gate::forUser($other)->allows('view', $serviceRequest));
    }

    public function test_assigned_provider_can_view_the_request(): void
    {
        $providerUser = User::factory()->create();
        $provider = ServiceProvider::factory()->create(['user_id' => $providerUser->id]);
        $serviceRequest = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->assertTrue(Gate::forUser($providerUser)->allows('view', $serviceRequest));
    }

    public function test_unassigned_provider_cannot_view_the_request(): void
    {
        $providerUser = User::factory()->create();
        ServiceProvider::factory()->create(['user_id' => $providerUser->id]);
        $serviceRequest = ServiceRequest::factory()->create();

        $this->assertFalse(Gate::forUser($providerUser)->allows('view', $serviceRequest));
    }

    public function test_admin_can_view_any_request(): void
    {
        $admin = User::factory()->admin()->create();
        $serviceRequest = ServiceRequest::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('view', $serviceRequest));
    }

    public function test_only_owner_or_admin_can_cancel_an_open_request(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $owner->id,
            'status' => ServiceRequestStatus::New,
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('cancel', $serviceRequest));
        $this->assertTrue(Gate::forUser($admin)->allows('cancel', $serviceRequest));
        $this->assertFalse(Gate::forUser($other)->allows('cancel', $serviceRequest));
    }

    public function test_completed_request_cannot_be_cancelled(): void
    {
        $owner = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['user_id' => $owner->id]);

        $this->assertFalse(Gate::forUser($owner)->allows('cancel', $serviceRequest));
    }

    public function test_provider_can_manage_status_only_when_assigned(): void
    {
        $providerUser = User::factory()->create();
        $provider = ServiceProvider::factory()->create(['user_id' => $providerUser->id]);
        $serviceRequest = ServiceRequest::factory()->assigned()->create([
            'service_provider_id' => $provider->id,
        ]);

        $this->assertTrue(Gate::forUser($providerUser)->allows('manageStatus', $serviceRequest));

        $otherProviderUser = User::factory()->create();
        ServiceProvider::factory()->create(['user_id' => $otherProviderUser->id]);

        $this->assertFalse(Gate::forUser($otherProviderUser)->allows('manageStatus', $serviceRequest));
    }

    public function test_suspended_user_cannot_create_a_request(): void
    {
        $suspended = User::factory()->suspended()->create();

        $this->assertFalse(Gate::forUser($suspended)->allows('create', ServiceRequest::class));
    }

    public function test_user_with_existing_provider_profile_cannot_create_another(): void
    {
        $user = User::factory()->create();
        ServiceProvider::factory()->create(['user_id' => $user->id]);

        $this->assertFalse(Gate::forUser($user)->allows('create', ServiceProvider::class));
    }

    public function test_pending_provider_profile_is_hidden_from_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $provider = ServiceProvider::factory()->pending()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $provider));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $provider));
        $this->assertFalse(Gate::forUser($other)->allows('view', $provider));
    }

    public function test_active_provider_profile_is_publicly_viewable(): void
    {
        $provider = ServiceProvider::factory()->create(['status' => ServiceProviderStatus::Active]);
        $viewer = User::factory()->create();

        $this->assertTrue(Gate::forUser($viewer)->allows('view', $provider));
    }

    public function test_review_requires_completed_request_owned_by_the_customer(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('review', $serviceRequest));
        $this->assertFalse(Gate::forUser($other)->allows('review', $serviceRequest));

        $openRequest = ServiceRequest::factory()->inProgress()->create(['user_id' => $owner->id]);
        $this->assertFalse(Gate::forUser($owner)->allows('review', $openRequest));
    }

    public function test_request_cannot_be_reviewed_twice(): void
    {
        $owner = User::factory()->create();
        $provider = ServiceProvider::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create([
            'user_id' => $owner->id,
            'service_provider_id' => $provider->id,
        ]);

        ServiceReview::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'service_provider_id' => $provider->id,
            'user_id' => $owner->id,
        ]);

        $serviceRequest->refresh();

        $this->assertFalse(Gate::forUser($owner)->allows('review', $serviceRequest));
    }
}
