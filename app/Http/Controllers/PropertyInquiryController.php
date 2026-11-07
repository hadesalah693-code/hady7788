<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PropertyInquiryController extends Controller
{
    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless(
            $property->status === PropertyStatus::Published
                && $property->published_at !== null
                && $property->published_at->lte(now()),
            404,
        );

        $key = 'property-inquiry:'.$request->ip().':'.$property->getKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'phone' => 'تم إرسال طلبات كثيرة لهذا العقار. حاول مرة أخرى بعد قليل.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            // Honeypot: browsers leave this hidden field empty; basic bots often fill it.
            'website' => ['prohibited'],
        ], [
            'name.required' => 'اكتب اسمك من فضلك.',
            'phone.required' => 'اكتب رقم الهاتف حتى نستطيع التواصل معك.',
            'phone.regex' => 'اكتب رقم هاتف صحيحًا.',
            'email.email' => 'اكتب بريدًا إلكترونيًا صحيحًا أو اتركه فارغًا.',
            'message.required' => 'اكتب تفاصيل الاستفسار أو طلب المعاينة.',
            'message.min' => 'اكتب تفاصيل أكثر عن طلبك.',
        ]);

        $property->inquiries()->create([
            'submitted_by' => $request->user()?->getKey(),
            'name' => trim($validated['name']),
            'phone' => preg_replace('/[\s\-()]/', '', $validated['phone']),
            'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            'message' => trim($validated['message']),
            'status' => 'new',
        ]);

        RateLimiter::hit($key, 300);

        return back()->with('inquiry_status', 'تم استلام طلبك بنجاح. سيتواصل معك فريق الإمداد لتنسيق المعاينة.');
    }
}
