<?php

namespace Database\Factories;

use App\Enums\ServiceProviderStatus;
use App\Models\City;
use App\Models\Governorate;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProvider>
 */
class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => fake('ar_EG')->name(),
            'phone' => '01'.fake()->numerify('#########'),
            'bio' => fake('ar_EG')->paragraph(2),
            'experience_years' => fake()->numberBetween(1, 20),
            'governorate_id' => Governorate::factory(),
            'city_id' => null,
            'service_areas' => [fake('ar_EG')->city(), fake('ar_EG')->city()],
            'status' => ServiceProviderStatus::Active,
            'is_available' => true,
            'rating_avg' => 0,
            'reviews_count' => 0,
            'completed_requests_count' => 0,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceProviderStatus::Pending,
            'approved_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceProviderStatus::Suspended,
            'is_available' => false,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (): array => ['is_available' => false]);
    }

    public function forCity(City $city): static
    {
        return $this->state(fn (): array => [
            'governorate_id' => $city->governorate_id,
            'city_id' => $city->getKey(),
        ]);
    }
}
