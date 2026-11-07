<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PropertyImageController extends Controller
{
    public function __invoke(PropertyImage $image): Response
    {
        $property = $image->property;

        // A soft-deleted property resolves to null through the relation, so
        // its images stop being reachable immediately.
        abort_if($property === null, 404);

        $isPublic = $property->status === PropertyStatus::Published
            && $property->published_at !== null
            && $property->published_at->lte(now());

        if (! $isPublic) {
            $user = auth()->user();

            // Hide the existence of private media from guests.
            abort_if($user === null, 404);

            Gate::authorize('view', $property);
        }

        if (! $image->isPrivate()) {
            if (str_starts_with($image->path, 'images/')) {
                return redirect()->away(asset($image->path));
            }

            return redirect()->away(Storage::disk($image->diskName())->url($image->path));
        }

        $disk = Storage::disk($image->diskName());

        abort_unless($disk->exists($image->path), 404);

        $extension = pathinfo($image->path, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = ($property->slug ?: 'property').'-'.$image->getKey().'.'.$extension;

        $headers = [
            'Content-Type' => $image->mime_type ?: $disk->mimeType($image->path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $isPublic ? 'public, max-age=300' : 'private, no-store',
        ];

        return $disk->response($image->path, $filename, $headers);
    }
}
