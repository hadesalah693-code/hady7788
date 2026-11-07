<?php

namespace App\Http\Controllers;

use App\Models\ServiceQuote;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceWorkflowController
{
    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->authorizeRequestAccess($request, $serviceRequest);
        $serviceRequest->load(['category', 'governorate', 'city', 'images', 'quotes.technician.user', 'technician.user', 'review']);
        return view('services.requests.show', compact('serviceRequest'));
    }

    public function quote(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $profile = $request->user()->technicianProfile;
        abort_unless($profile && $profile->verification_status === 'approved' && $profile->availability_status === 'available', 403);
        abort_unless($serviceRequest->status === 'pending' && $serviceRequest->technician_profile_id === null, 409);
        abort_unless($profile->specializations()->where('service_category_id', $serviceRequest->service_category_id)->exists(), 403);
        abort_unless($profile->serviceAreas()->where('governorate_id', $serviceRequest->governorate_id)->exists(), 403);
        abort_if($serviceRequest->customer_id === $request->user()->id, 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'details' => ['nullable', 'string', 'max:2000'],
            'estimated_duration_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
        ]);

        ServiceQuote::updateOrCreate(
            ['service_request_id' => $serviceRequest->id, 'technician_profile_id' => $profile->id],
            ['amount' => $data['amount'], 'currency' => 'EGP', 'details' => $data['details'] ?? null,
             'estimated_duration_hours' => $data['estimated_duration_hours'] ?? null, 'status' => 'pending', 'expires_at' => now()->addDays(3)]
        );

        return back()->with('status', 'تم إرسال عرض السعر للعميل.');
    }

    public function acceptQuote(Request $request, ServiceRequest $serviceRequest, ServiceQuote $quote): RedirectResponse
    {
        abort_unless($serviceRequest->customer_id === $request->user()->id, 403);
        DB::transaction(function () use ($serviceRequest, $quote): void {
            $lockedRequest = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            $lockedQuote = ServiceQuote::query()->lockForUpdate()->where('service_request_id', $lockedRequest->id)->findOrFail($quote->id);
            if ($lockedRequest->status !== 'pending' || $lockedRequest->technician_profile_id !== null || $lockedQuote->status !== 'pending' || ($lockedQuote->expires_at && $lockedQuote->expires_at->isPast())) {
                throw ValidationException::withMessages(['quote' => 'هذا العرض لم يعد متاحًا للقبول.']);
            }
            ServiceQuote::where('service_request_id', $lockedRequest->id)->whereKeyNot($lockedQuote->id)->where('status', 'pending')->update(['status' => 'rejected']);
            $lockedQuote->update(['status' => 'accepted', 'accepted_at' => now()]);
            $lockedRequest->update(['technician_profile_id' => $lockedQuote->technician_profile_id, 'status' => 'accepted', 'accepted_at' => now()]);
        });
        return back()->with('status', 'تم قبول عرض السعر وتحديد الفني.');
    }

    public function schedule(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_unless($serviceRequest->technician?->user_id === $request->user()->id, 403);
        abort_unless(in_array($serviceRequest->status, ['accepted', 'scheduled'], true), 409);
        $data = $request->validate(['scheduled_at' => ['required', 'date', 'after:now']]);
        $serviceRequest->update(['scheduled_at' => $data['scheduled_at'], 'status' => 'scheduled']);
        return back()->with('status', 'تم تحديد موعد الزيارة.');
    }

    public function complete(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_unless($serviceRequest->technician?->user_id === $request->user()->id, 403);
        abort_unless(in_array($serviceRequest->status, ['accepted', 'scheduled', 'in_progress'], true), 409);
        $serviceRequest->update(['status' => 'completed', 'completed_at' => now()]);
        return back()->with('status', 'تم تسجيل اكتمال الخدمة، ويمكن للعميل تقييمها الآن.');
    }

    public function review(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_unless($serviceRequest->customer_id === $request->user()->id, 403);
        abort_unless($serviceRequest->status === 'completed' && $serviceRequest->technician_profile_id, 409);
        abort_if($serviceRequest->review()->exists(), 409);
        $data = $request->validate(['rating' => ['required', 'integer', 'min:1', 'max:5'], 'comment' => ['nullable', 'string', 'max:2000']]);
        ServiceReview::create([
            'service_request_id' => $serviceRequest->id, 'customer_id' => $request->user()->id,
            'technician_profile_id' => $serviceRequest->technician_profile_id,
            'rating' => $data['rating'], 'comment' => $data['comment'] ?? null, 'is_visible' => true,
        ]);
        return back()->with('status', 'شكرًا لك، تم إرسال تقييمك.');
    }

    private function authorizeRequestAccess(Request $request, ServiceRequest $serviceRequest): void
    {
        $user = $request->user();
        abort_unless($user && ($user->is_admin || $serviceRequest->customer_id === $user->id || $serviceRequest->technician?->user_id === $user->id), 404);
    }
}
