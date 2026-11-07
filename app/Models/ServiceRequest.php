<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['customer_id', 'service_category_id', 'technician_profile_id', 'governorate_id', 'city_id', 'title', 'description', 'private_address', 'preferred_time', 'status', 'scheduled_at', 'completed_at', 'accepted_at'])]
class ServiceRequest extends Model
{
    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'completed_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function category(): BelongsTo { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
    public function technician(): BelongsTo { return $this->belongsTo(TechnicianProfile::class, 'technician_profile_id'); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function images(): HasMany { return $this->hasMany(ServiceRequestImage::class); }
    public function quotes(): HasMany { return $this->hasMany(ServiceQuote::class); }
    public function review(): HasOne { return $this->hasOne(ServiceReview::class); }
}
