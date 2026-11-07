<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_request_id', 'customer_id', 'technician_profile_id', 'rating', 'comment', 'is_visible'])]
class ServiceReview extends Model
{
    protected function casts(): array { return ['rating' => 'integer', 'is_visible' => 'boolean']; }
    public function request(): BelongsTo { return $this->belongsTo(ServiceRequest::class, 'service_request_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function technician(): BelongsTo { return $this->belongsTo(TechnicianProfile::class, 'technician_profile_id'); }
}
