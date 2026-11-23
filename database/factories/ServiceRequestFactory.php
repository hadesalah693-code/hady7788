<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'reference' => 'SR-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'service_provider_id' => null,
            'governorate_id' => Governorate::factory(),
            'city_id' => null,
            'district_id' => null,
            'title' => fake('ar_EG')->sentence(4),
            'description' => fake('ar_EG')->paragraph(3),
            'address' => fake('ar_EG')->address(),
            'contact_phone' => '01'.fake()->numerify('#########'),
            'preferred_date' => now()->addDays(fake()->numberBetween(1, 10))->toDateString(),
            'preferred_time' => fake()->randomElement(['09:00', '12:00', '15:00', '18:00']),
            'status' => ServiceRequestStatus::New,
            'quoted_price' => null,
            'admin_notes' => null,
            'cancellation_reason' => null,
            'assigned_at' => null,
            'accepted_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceRequestStatus::Assigned,
            'assigned_at' => now(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceRequestStatus::Accepted,
            'assigned_at' => now()->subHour(),
            'accepted_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceRequestStatus::InProgress,
            'assigned_at' => now()->subHours(2),
            'accepted_at' => now()->subHour(),
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceRequestStatus::Completed,
            'assigned_at' => now()->subDays(2),
            'accepted_at' => now()->subDay(),
            'started_at' => now()->subHours(5),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceRequestStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
