<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PropertyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_and_delete_their_property(): void
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $property));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $property));
    }

    public function test_other_user_cannot_update_or_delete_someone_elses_property(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse(Gate::forUser($other)->allows('update', $property));
        $this->assertFalse(Gate::forUser($other)->allows('delete', $property));
    }

    public function test_admin_can_update_and_delete_any_property(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($admin)->allows('update', $property));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $property));
    }

    public function test_suspended_user_cannot_create_a_property(): void
    {
        $suspended = User::factory()->suspended()->create();

        $this->assertFalse(Gate::forUser($suspended)->allows('create', Property::class));
    }

    public function test_published_property_is_viewable_by_any_authenticated_user(): void
    {
        $viewer = User::factory()->create();
        $property = Property::factory()->create([
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->assertTrue(Gate::forUser($viewer)->allows('view', $property));
    }
}
