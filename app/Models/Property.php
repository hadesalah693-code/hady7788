<?php

namespace App\Models;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'governorate_id',
    'city_id',
    'district_id',
    'property_type_id',
    'purpose',
    'status',
    'title',
    'slug',
    'description',
    'price',
    'currency',
    'area',
    'bedrooms',
    'bathrooms',
    'floor',
    'total_floors',
    'parking_spaces',
    'year_built',
    'finishing',
    'available_from',
    'address',
    'contact_name',
    'contact_phone',
    'contact_whatsapp',
    'latitude',
    'longitude',
    'is_featured',
    'published_at',
    'published_by',
    'views_count',
    'rejection_reason',
    'archive_reason',
])]
class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'purpose' => PropertyPurpose::class,
            'status' => PropertyStatus::class,
            'price' => 'decimal:2',
            'area' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'available_from' => 'date',
            'views_count' => 'integer',
            'total_floors' => 'integer',
            'parking_spaces' => 'integer',
            'year_built' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property) {
            if (blank($property->slug)) {
                $base = Str::slug($property->title) ?: 'property';

                do {
                    $slug = $base.'-'.Str::lower(Str::random(6));
                } while (static::withTrashed()->where('slug', $slug)->exists());

                $property->slug = $slug;
            }
        });

        // Soft deletes keep the files so a property can be restored. A force
        // delete is permanent, so remove any privately stored media as well.
        static::forceDeleting(function (Property $property) {
            $property->images()->get()->each(function (PropertyImage $image): void {
                if ($image->isPrivate()) {
                    Storage::disk($image->diskName())->delete($image->path);
                }
            });
        });
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

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->where('is_cover', true)->orderBy('sort_order');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PropertyStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfPurpose(Builder $query, PropertyPurpose|string|null $purpose): Builder
    {
        if ($purpose instanceof PropertyPurpose) {
            $purpose = $purpose->value;
        }

        return $query->when($purpose, fn (Builder $query) => $query->where('purpose', $purpose));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $image = $this->coverImage
                    ?? ($this->relationLoaded('images') ? $this->images->first() : null);

                return $image?->url ?? asset('images/demo/placeholder.svg');
            },
        );
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (): string => number_format((float) $this->price, 0, '.', ','),
        );
    }

    protected function currencyLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->currency) {
                'USD' => 'دولار أمريكي',
                'EUR' => 'يورو',
                default => 'ج.م',
            },
        );
    }

    protected function priceSuffix(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->purpose === PropertyPurpose::Rent ? ' / شهر' : '',
        );
    }

    protected function areaFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (): string => number_format((float) $this->area, 0, '.', ',').' م²',
        );
    }

    protected function purposeLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->purpose?->label() ?? '',
        );
    }

    protected function locationLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => collect([
                $this->district?->name_ar,
                $this->city?->name_ar,
                $this->governorate?->name_ar,
            ])->filter()->unique()->implode('، '),
        );
    }

    protected function isNew(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->published_at?->gt(now()->subDays(7)) ?? false,
        );
    }
}
