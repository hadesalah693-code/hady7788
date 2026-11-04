<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['property_id', 'path', 'disk', 'mime_type', 'size', 'alt', 'sort_order', 'is_cover'])]
class PropertyImage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'size' => 'integer',
            'is_cover' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function diskName(): string
    {
        return $this->disk ?: 'public';
    }

    public function isPrivate(): bool
    {
        return $this->diskName() === config('properties.images.disk', 'property-images');
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
                    return $this->path;
                }

                // Legacy demo images live in the public web root.
                if (str_starts_with($this->path, 'images/')) {
                    return asset($this->path);
                }

                if ($this->isPrivate()) {
                    return route('properties.images.show', $this);
                }

                return Storage::disk($this->diskName())->url($this->path);
            },
        );
    }
}
