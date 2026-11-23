<?php

namespace App\Models;

use App\Enums\ServiceProviderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'display_name',
    'phone',
    'bio',
    'experience_years',
    'governorate_id',
    'city_id',
    'service_areas',
    'status',
    'is_available',
    'approved_at',
    'approved_by',
    'rejection_reason',
])]
class ServiceProvider extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'service_areas' => 'array',
            'status' => ServiceProviderStatus::class,
            'is_available' => 'boolean',
            'rating_avg' => 'decimal:2',
            'reviews_count' => 'integer',
            'completed_requests_count' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_provider_categories')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceProviderImage::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ServiceCommission::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ServiceProviderStatus::Active);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->where('is_available', true);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', fn (Builder $relation): Builder => $relation->whereKey($categoryId));
    }

    public function isActive(): bool
    {
        return $this->status === ServiceProviderStatus::Active;
    }

    public function canReceiveRequests(): bool
    {
        return $this->isActive() && $this->is_available;
    }

    public function recalculateRating(): void
    {
        $reviews = $this->reviews();

        $this->forceFill([
            'reviews_count' => (clone $reviews)->count(),
            'rating_avg' => (float) ((clone $reviews)->avg('rating') ?? 0),
        ])->save();
    }
}
