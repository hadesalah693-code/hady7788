<?php

namespace App\Models;

use App\Enums\AmenityCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name_ar', 'name_en', 'slug', 'category', 'sort_order', 'icon', 'is_active'])]
class Amenity extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => AmenityCategory::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_amenity');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name_ar');
    }
}
