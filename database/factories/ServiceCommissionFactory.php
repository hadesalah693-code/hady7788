<?php

namespace Database\Factories;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Models\ServiceCommission;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCommission>
 */
class ServiceCommissionFactory extends Factory
{
    protected $model = ServiceCommission::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'type' => CommissionType::Fixed,
            'rate' => 50,
            'amount' => 50,
            'status' => CommissionStatus::Pending,
            'settled_at' => null,
        ];
    }

    public function settled(): static
    {
        return $this->state(fn (): array => [
            'status' => CommissionStatus::Settled,
            'settled_at' => now(),
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (): array => [
            'status' => CommissionStatus::Waived,
        ]);
    }
}
