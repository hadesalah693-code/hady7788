<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'service_provider_id',
    'type',
    'rate',
    'amount',
    'status',
    'settled_at',
])]
class ServiceCommission extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => CommissionType::class,
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'status' => CommissionStatus::class,
            'settled_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
