<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference',
    'user_id',
    'service_category_id',
    'service_provider_id',
    'governorate_id',
    'city_id',
    'district_id',
    'title',
    'description',
    'address',
    'contact_phone',
    'preferred_date',
    'preferred_time',
    'status',
    'quoted_price',
    'admin_notes',
    'cancellation_reason',
    'assigned_at',
    'accepted_at',
    'started_at',
    'completed_at',
    'cancelled_at',
])]
class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ServiceRequestStatus::class,
            'preferred_date' => 'date',
            'quoted_price' => 'decimal:2',
            'assigned_at' => 'datetime',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceRequestImage::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(ServiceReview::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(ServiceCommission::class);
    }

    public function scopeForCustomer(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('service_provider_id', $providerId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ServiceRequestStatus::Completed,
            ServiceRequestStatus::Cancelled,
        ]);
    }

    public function isOwnedBy(User $user): bool
    {
        return $user->isAdmin() || (int) $this->user_id === (int) $user->getKey();
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->provider !== null
            && (int) $this->provider->user_id === (int) $user->getKey();
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->status === ServiceRequestStatus::Completed
            && (int) $this->user_id === (int) $user->getKey()
            && $this->review === null;
    }

    public function providerContactVisible(): bool
    {
        return in_array($this->status, [
            ServiceRequestStatus::Accepted,
            ServiceRequestStatus::InProgress,
            ServiceRequestStatus::Completed,
        ], true);
    }
}
