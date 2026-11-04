<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\BrokerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'company_id',
    'professional_title',
    'license_number',
    'years_of_experience',
    'bio',
    'service_areas',
    'verification_status',
    'verified_at',
    'verified_by',
])]
class BrokerProfile extends Model
{
    /** @use HasFactory<BrokerProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'years_of_experience' => 'integer',
            'service_areas' => 'array',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
