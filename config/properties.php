<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Property media
    |--------------------------------------------------------------------------
    |
    | Uploaded property images are stored on a private disk and served through
    | a guarded route, so unpublished listings never leak through a public
    | URL. The limits below can be tuned through the environment without a
    | code change.
    |
    */

    'images' => [
        'disk' => env('PROPERTY_IMAGE_DISK', 'property-images'),

        // Maximum size for a single image, in kilobytes (default 5 MB).
        'max_kb' => (int) env('PROPERTY_IMAGE_MAX_KB', 5120),

        // Maximum number of images attached to one property.
        'max_per_property' => (int) env('PROPERTY_IMAGES_MAX', 20),

        'min_width' => 200,
        'min_height' => 200,
        'max_width' => 6000,
        'max_height' => 6000,

        // Hard cap on decoded pixels to protect server memory. 36 MP covers
        // the 6000 x 6000 envelope while rejecting decompression bombs.
        'max_pixels' => (int) env('PROPERTY_IMAGE_MAX_PIXELS', 36000000),

        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],

        'mimetypes' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

];
