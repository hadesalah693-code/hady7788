<?php

namespace Database\Factories;

use App\Enums\CommissionType;
use App\Models\CommissionSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionSetting>
 */
class CommissionSettingFactory extends Factory
{
    protected $model = CommissionSetting::class;

    public function definition(): array
    {
        return [
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 50,
            'percentage_rate' => 5,
            'min_amount' => 0,
            'is_active' => true,
        ];
    }

    public function percentage(float $rate = 5, float $min = 0): static
    {
        return $this->state(fn (): array => [
            'commission_type' => CommissionType::Percentage,
            'percentage_rate' => $rate,
            'min_amount' => $min,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
