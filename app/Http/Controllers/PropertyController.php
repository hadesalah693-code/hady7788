<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Contracts\View\View;

class PropertyController extends Controller
{
    public function show(Property $property): View
    {
        abort_unless(
            $property->status === PropertyStatus::Published && $property->published_at?->isPast(),
            404,
        );

        $property->load(['user', 'images', 'coverImage', 'city', 'district', 'governorate', 'type', 'amenities' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name_ar')]);
        $property->increment('views_count');

        $similarProperties = Property::query()
            ->published()
            ->whereKeyNot($property->getKey())
            ->where(function ($query) use ($property) {
                $query->where('property_type_id', $property->property_type_id)
                    ->orWhere('governorate_id', $property->governorate_id);
            })
            ->with(['coverImage', 'city', 'district', 'governorate', 'type'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('properties.show', compact('property', 'similarProperties'));
    }
}
