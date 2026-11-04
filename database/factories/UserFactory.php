<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'account_type' => AccountType::User,
            'status' => AccountStatus::Active,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'status' => AccountStatus::Active,
        ]);
    }

    public function broker(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Broker,
            'status' => AccountStatus::Pending,
        ])->afterCreating(function (User $user): void {
            $user->brokerProfile()->create([
                'professional_title' => 'وسيط عقاري',
                'verification_status' => VerificationStatus::Pending,
            ]);
        });
    }

    public function verifiedBroker(): static
    {
        return $this->broker()
            ->state(fn (array $attributes) => [
                'status' => AccountStatus::Active,
            ])
            ->afterCreating(function (User $user): void {
                $user->brokerProfile()->update([
                    'verification_status' => VerificationStatus::Verified,
                    'verified_at' => now(),
                ]);
            });
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Company,
            'status' => AccountStatus::Pending,
        ])->afterCreating(function (User $user): void {
            $user->companyProfile()->create([
                'company_name' => fake()->company(),
                'verification_status' => VerificationStatus::Pending,
            ]);
        });
    }

    public function verifiedCompany(): static
    {
        return $this->company()
            ->state(fn (array $attributes) => [
                'status' => AccountStatus::Active,
            ])
            ->afterCreating(function (User $user): void {
                $user->companyProfile()->update([
                    'verification_status' => VerificationStatus::Verified,
                    'verified_at' => now(),
                ]);
            });
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Suspended,
        ]);
    }
}
