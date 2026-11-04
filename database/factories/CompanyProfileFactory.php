<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyProfile>
 */
class CompanyProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'commercial_register' => fake()->numerify('CR-######'),
            'tax_id' => fake()->numerify('###-###-###'),
            'website' => fake()->url(),
            'address' => fake('ar_EG')->address(),
            'description' => fake('ar_EG')->paragraph(3),
            'verification_status' => VerificationStatus::Pending,
        ];
    }
}
