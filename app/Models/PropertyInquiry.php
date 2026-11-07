<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'property_id',
    'submitted_by',
    'name',
    'phone',
    'email',
    'message',
    'status',
    'internal_notes',
])]
class PropertyInquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_CLOSED = 'closed';

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
