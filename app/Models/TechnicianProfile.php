<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'display_name', 'professional_title', 'bio', 'years_experience', 'verification_status', 'availability_status', 'approval_notes', 'verified_by', 'verified_at'])]
class TechnicianProfile extends Model
{
    protected function casts(): array
    {
        return ['years_experience' => 'integer', 'verified_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function specializations(): HasMany { return $this->hasMany(TechnicianSpecialization::class); }
    public function serviceAreas(): HasMany { return $this->hasMany(TechnicianServiceArea::class); }
    public function quotes(): HasMany { return $this->hasMany(ServiceQuote::class); }
    public function reviews(): HasMany { return $this->hasMany(ServiceReview::class); }

    public function scopeApproved($query) { return $query->where('verification_status', 'approved'); }
    public function scopeAvailable($query) { return $query->where('availability_status', 'available'); }

    public function averageRating(): float
    {
        return (float) $this->reviews()->where('is_visible', true)->avg('rating');
    }
}
