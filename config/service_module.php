<?php

use App\Enums\CommissionType;

return [

    /*
    |--------------------------------------------------------------------------
    | Service request images
    |--------------------------------------------------------------------------
    |
    | Customer uploaded photos are stored on a private disk and served through
    | a guarded route so only the customer, the assigned provider and admins
    | can open them.
    |
    */

    'images' => [
        'disk' => env('SERVICE_IMAGE_DISK', 'service-images'),

        'max_kb' => (int) env('SERVICE_IMAGE_MAX_KB', 5120),
        'max_per_request' => (int) env('SERVICE_IMAGES_PER_REQUEST', 5),
        'max_per_provider' => (int) env('SERVICE_IMAGES_PER_PROVIDER', 8),

        'min_width' => 200,
        'min_height' => 200,
        'max_width' => 6000,
        'max_height' => 6000,
        'max_pixels' => 36_000_000,

        'mimetypes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission defaults
    |--------------------------------------------------------------------------
    |
    | These values are used only until an administrator creates a settings row
    | from the admin panel. After that, the database row always wins.
    |
    */

    'commission' => [
        'type' => env('SERVICE_COMMISSION_TYPE', CommissionType::Fixed->value),
        'fixed_amount' => (float) env('SERVICE_COMMISSION_FIXED', 50),
        'percentage_rate' => (float) env('SERVICE_COMMISSION_PERCENTAGE', 5),
        'min_amount' => (float) env('SERVICE_COMMISSION_MIN', 0),
    ],

    'requests' => [
        // Maximum number of open requests a single customer may have.
        'max_open_per_customer' => (int) env('SERVICE_MAX_OPEN_REQUESTS', 10),
    ],

];
