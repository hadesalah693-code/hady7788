<?php

namespace Tests\Feature\Account;

use App\Enums\PropertyStatus;
use App\Livewire\Account\Dashboard;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_only_counts_the_authenticated_user_properties(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Property::factory()->count(2)->create(['user_id' => $user->id]);
        Property::factory()->create(['user_id' => $user->id, 'status' => PropertyStatus::Pending]);
        Property::factory()->count(4)->create(['user_id' => $other->id]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertViewHas('propertiesCount', 3)
            ->assertViewHas('publishedCount', 2)
            ->assertViewHas('latestProperties', function ($properties) use ($user): bool {
                return $properties->count() === 3
                    && $properties->every(fn (Property $property): bool => $property->user_id === $user->id);
            });
    }

    public function test_it_shows_the_authenticated_account_information(): void
    {
        $user = User::factory()->company()->create(['name' => 'شركة الإمداد للتطوير']);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('شركة الإمداد للتطوير')
            ->assertSee($user->email)
            ->assertSee($user->account_type->label())
            ->assertSee(__('app.account.dashboard.stats.properties'));
    }

    public function test_it_renders_the_empty_state_without_properties(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertViewHas('propertiesCount', 0)
            ->assertViewHas('publishedCount', 0)
            ->assertSee(__('app.account.dashboard.properties_empty'));
    }

    public function test_it_only_lists_the_latest_three_properties(): void
    {
        $user = User::factory()->create();

        Property::factory()->count(5)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertViewHas('latestProperties', fn ($properties): bool => $properties->count() === 3);
    }
}
