<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceReview>
 */
class ServiceReviewFactory extends Factory
{
    protected $model = ServiceReview::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake('ar_EG')->sentence(),
            'is_reported' => false,
            'report_reason' => null,
            'reported_at' => null,
        ];
    }

    public function reported(): static
    {
        return $this->state(fn (): array => [
            'is_reported' => true,
            'report_reason' => fake('ar_EG')->sentence(),
            'reported_at' => now(),
        ]);
    }
}
