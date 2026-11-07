<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['technician_profile_id', 'governorate_id', 'city_id'])]
class TechnicianServiceArea extends Model
{
    public function technician(): BelongsTo { return $this->belongsTo(TechnicianProfile::class, 'technician_profile_id'); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
}
