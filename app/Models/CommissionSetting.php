<?php

namespace App\Models;

use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'commission_type',
    'fixed_amount',
    'percentage_rate',
    'min_amount',
    'is_active',
])]
class CommissionSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'commission_type' => CommissionType::class,
            'fixed_amount' => 'decimal:2',
            'percentage_rate' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The active settings row. When the administrator has not created one yet,
     * an unsaved model populated from config is returned so the module keeps
     * working out of the box.
     */
    public static function current(): self
    {
        $setting = static::query()->where('is_active', true)->latest('id')->first();

        if ($setting !== null) {
            return $setting;
        }

        $setting = new static;
        $setting->forceFill([
            'commission_type' => config('service_module.commission.type', CommissionType::Fixed->value),
            'fixed_amount' => config('service_module.commission.fixed_amount', 50),
            'percentage_rate' => config('service_module.commission.percentage_rate', 5),
            'min_amount' => config('service_module.commission.min_amount', 0),
            'is_active' => true,
        ]);

        return $setting;
    }

    /**
     * @return array{type: CommissionType, rate: float, amount: float}
     */
    public function calculate(?float $serviceValue): array
    {
        $type = $this->commission_type instanceof CommissionType
            ? $this->commission_type
            : CommissionType::from((string) $this->commission_type);

        $rate = $type === CommissionType::Fixed
            ? (float) $this->fixed_amount
            : (float) $this->percentage_rate;

        $amount = $type === CommissionType::Fixed
            ? $rate
            : round(((float) $serviceValue) * ($rate / 100), 2);

        if ($type === CommissionType::Percentage && $serviceValue !== null) {
            $amount = max($amount, (float) $this->min_amount);
        }

        return [
            'type' => $type,
            'rate' => $rate,
            'amount' => round(max($amount, 0), 2),
        ];
    }
}
