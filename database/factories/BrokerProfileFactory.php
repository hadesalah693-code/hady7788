<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\BrokerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrokerProfile>
 */
class BrokerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'professional_title' => 'وسيط عقاري',
            'license_number' => fake()->numerify('BR-######'),
            'years_of_experience' => fake()->numberBetween(1, 20),
            'bio' => fake('ar_EG')->paragraph(3),
            'service_areas' => ['القاهرة', 'الجيزة'],
            'verification_status' => VerificationStatus::Pending,
        ];
    }
}
