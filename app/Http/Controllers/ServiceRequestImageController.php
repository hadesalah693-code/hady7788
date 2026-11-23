<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestImage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ServiceRequestImageController extends Controller
{
    public function __invoke(ServiceRequestImage $image): Response
    {
        $serviceRequest = $image->request;

        abort_if($serviceRequest === null, 404);

        $user = auth()->user();

        // Request photos are private: hide their existence from guests.
        abort_if($user === null, 404);

        Gate::authorize('view', $serviceRequest);

        $disk = Storage::disk($image->disk ?: 'service-images');

        abort_unless($disk->exists($image->path), 404);

        $extension = pathinfo($image->path, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'service-request-'.$serviceRequest->getKey().'-'.$image->getKey().'.'.$extension;

        return $disk->response($image->path, $filename, [
            'Content-Type' => $image->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
