<?php

namespace Tests\Feature\Accounts;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublishingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'can:publish-properties'])
            ->post('/__tests/publish-property', fn (): string => 'published');
    }

    public function test_unverified_broker_is_denied_publishing_from_the_backend(): void
    {
        $broker = User::factory()->broker()->create();

        $this->actingAs($broker)
            ->post('/__tests/publish-property')
            ->assertForbidden();
    }

    public function test_unverified_company_is_denied_publishing_from_the_backend(): void
    {
        $company = User::factory()->company()->create();

        $this->actingAs($company)
            ->post('/__tests/publish-property')
            ->assertForbidden();
    }

    public function test_verified_broker_can_publish_from_the_backend(): void
    {
        $broker = User::factory()->verifiedBroker()->create();

        $this->actingAs($broker)
            ->post('/__tests/publish-property')
            ->assertOk()
            ->assertSee('published');
    }

    public function test_verified_company_can_publish_from_the_backend(): void
    {
        $company = User::factory()->verifiedCompany()->create();

        $this->actingAs($company)
            ->post('/__tests/publish-property')
            ->assertOk()
            ->assertSee('published');
    }

    public function test_regular_user_cannot_publish_from_the_backend(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/__tests/publish-property')
            ->assertForbidden();
    }

    public function test_guests_cannot_publish_from_the_backend(): void
    {
        $this->post('/__tests/publish-property')->assertRedirect(route('login'));
    }

    public function test_suspended_verified_broker_cannot_publish_from_the_backend(): void
    {
        $broker = User::factory()->verifiedBroker()->create([
            'status' => AccountStatus::Suspended,
        ]);

        $this->actingAs($broker)
            ->post('/__tests/publish-property')
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_publish_from_the_backend(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/__tests/publish-property')
            ->assertOk();
    }
}
