<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestImage;
use App\Models\TechnicianProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceRequestController
{
    public function create(): View
    {
        return view('services.requests.create', [
            'categories' => ServiceCategory::active()->orderBy('sort_order')->get(),
            'governorates' => Governorate::where('is_active', true)->orderBy('name_ar')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'governorate_id' => ['required', 'exists:governorates,id'],
            'city_id' => ['nullable', Rule::exists('cities', 'id')->where(fn ($query) => $query->where('governorate_id', $request->integer('governorate_id')))],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:15', 'max:5000'],
            'private_address' => ['required', 'string', 'min:8', 'max:1000'],
            'preferred_time' => ['nullable', 'string', 'max:80'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $serviceRequest = DB::transaction(function () use ($data, $request): ServiceRequest {
            $serviceRequest = ServiceRequest::create([
                ...collect($data)->except('images')->all(),
                'customer_id' => $request->user()->id,
                'status' => 'pending',
            ]);

            foreach ($request->file('images', []) as $image) {
                $path = $image->store('service-requests/'.$serviceRequest->id, 'local');
                if (! $path) {
                    throw new \\RuntimeException('Unable to store service request image.');
                }
                $serviceRequest->images()->create([
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }

            return $serviceRequest;
        });

        return redirect()->route('services.requests.show', $serviceRequest)->with('status', 'تم إرسال طلب الصيانة بنجاح، وسيتمكن الفنيون المعتمدون في منطقتك من تقديم عروض الأسعار.');
    }

    public function applyForm(): View
    {
        abort_if(auth()->user()->technicianProfile()->exists(), 403);

        return view('services.technicians.apply', [
            'categories' => ServiceCategory::active()->orderBy('sort_order')->get(),
            'governorates' => Governorate::where('is_active', true)->orderBy('name_ar')->get(),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        abort_if($request->user()->technicianProfile()->exists(), 403);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'professional_title' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', 'exists:service_categories,id'],
            'governorates' => ['required', 'array', 'min:1'],
            'governorates.*' => ['integer', 'distinct', 'exists:governorates,id'],
        ]);

        $profile = TechnicianProfile::create([
            'user_id' => $request->user()->id,
            'display_name' => $data['display_name'],
            'professional_title' => $data['professional_title'] ?? null,
            'bio' => $data['bio'] ?? null,
            'years_experience' => $data['years_experience'],
            'verification_status' => 'pending',
            'availability_status' => 'available',
        ]);

        foreach ($data['categories'] as $categoryId) {
            $profile->specializations()->create(['service_category_id' => $categoryId, 'accepts_quotes' => true]);
        }

        foreach ($data['governorates'] as $governorateId) {
            $profile->serviceAreas()->create(['governorate_id' => $governorateId]);
        }

        return redirect()->route('account.dashboard')->with('status', 'تم استلام طلب انضمامك كفني. سيظهر ملفك للعملاء بعد موافقة الإدارة.');
    }
}
