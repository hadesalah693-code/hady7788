<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name_ar', 'name_en', 'slug', 'icon', 'sort_order', 'is_active'])]
class PropertyType extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function iconComponent(): string
    {
        return match ($this->icon) {
            'villa' => 'heroicon-o-home-modern',
            'townhouse' => 'heroicon-o-home',
            'land' => 'heroicon-o-map',
            'shop' => 'heroicon-o-building-storefront',
            'office' => 'heroicon-o-building-office',
            'chalet' => 'heroicon-o-sun',
            'building' => 'heroicon-o-building-library',
            default => 'heroicon-o-building-office-2',
        };
    }
}
