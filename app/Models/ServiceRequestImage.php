<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_request_id', 'disk', 'path', 'original_name'])]
class ServiceRequestImage extends Model
{
    public function request(): BelongsTo { return $this->belongsTo(ServiceRequest::class, 'service_request_id'); }
}
