<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredProperties = Property::query()
            ->published()
            ->featured()
            ->with(['coverImage', 'city', 'district', 'governorate', 'type'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $propertyTypes = PropertyType::query()
            ->where('is_active', true)
            ->withCount(['properties' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get();

        $governorates = Governorate::query()
            ->where('is_active', true)
            ->whereHas('properties', fn ($query) => $query->published())
            ->withCount(['properties' => fn ($query) => $query->published()])
            ->orderByDesc('properties_count')
            ->take(8)
            ->get();

        $governorateOptions = Governorate::query()
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'slug']);

        $stats = [
            'properties' => Property::query()->published()->count(),
            'governorates' => $governorates->count(),
            'types' => $propertyTypes->count(),
        ];

        return view('home', compact('featuredProperties', 'propertyTypes', 'governorates', 'governorateOptions', 'stats'));
    }
}
