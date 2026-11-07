<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'name_ar', 'slug', 'description', 'icon', 'is_active', 'sort_order'])]
class ServiceCategory extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    public function technicianSpecializations(): HasMany { return $this->hasMany(TechnicianSpecialization::class); }
    public function requests(): HasMany { return $this->hasMany(ServiceRequest::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
