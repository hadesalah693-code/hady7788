<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\TechnicianProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceTechnicianController
{
    public function index(Request $request): View
    {
        $technicians = TechnicianProfile::query()
            ->approved()
            ->available()
            ->whereHas('specializations', fn ($query) => $query->where('service_category_id', $request->integer('category')))
            ->with(['user.governorate', 'specializations.category', 'serviceAreas.governorate', 'reviews'])
            ->when($request->filled('governorate'), fn ($query) => $query->whereHas('serviceAreas', fn ($area) => $area->where('governorate_id', $request->integer('governorate'))))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('services.technicians.index', [
            'technicians' => $technicians,
            'categories' => ServiceCategory::active()->whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
        ]);
    }

    public function show(TechnicianProfile $technician): View
    {
        abort_unless($technician->verification_status === 'approved' && $technician->availability_status === 'available', 404);

        $technician->load(['specializations.category', 'serviceAreas.governorate', 'serviceAreas.city', 'reviews' => fn ($query) => $query->where('is_visible', true)->latest(), 'user']);

        return view('services.technicians.show', compact('technician'));
    }
}
