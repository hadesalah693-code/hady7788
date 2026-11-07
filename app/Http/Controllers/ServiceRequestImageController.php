<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ServiceRequestImageController
{
    public function __invoke(Request $request, ServiceRequestImage $image): Response
    {
        $serviceRequest = $image->request()->with('technician')->firstOrFail();
        $user = $request->user();
        abort_unless($user && ($user->is_admin || $serviceRequest->customer_id === $user->id || $serviceRequest->technician?->user_id === $user->id), 404);

        $disk = Storage::disk($image->disk);
        abort_unless($disk->exists($image->path), 404);

        return $disk->response($image->path, basename($image->path), [
            'Content-Type' => $disk->mimeType($image->path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
