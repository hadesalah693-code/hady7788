<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_request_id', 'technician_profile_id', 'amount', 'currency', 'details', 'estimated_duration_hours', 'status', 'expires_at', 'accepted_at'])]
class ServiceQuote extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function request(): BelongsTo { return $this->belongsTo(ServiceRequest::class, 'service_request_id'); }
    public function technician(): BelongsTo { return $this->belongsTo(TechnicianProfile::class, 'technician_profile_id'); }
}
