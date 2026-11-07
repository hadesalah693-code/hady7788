<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['technician_profile_id', 'service_category_id', 'starting_price', 'accepts_quotes'])]
class TechnicianSpecialization extends Model
{
    protected function casts(): array { return ['starting_price' => 'decimal:2', 'accepts_quotes' => 'boolean']; }
    public function technician(): BelongsTo { return $this->belongsTo(TechnicianProfile::class, 'technician_profile_id'); }
    public function category(): BelongsTo { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
}
