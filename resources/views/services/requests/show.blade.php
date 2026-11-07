<x-layouts.app>
<section class="mx-auto max-w-4xl px-4 py-10" dir="rtl">
    <h1 class="text-2xl font-bold">طلب الصيانة: {{ $serviceRequest->title }}</h1>
    @if(session('status'))<p class="mt-4 rounded-xl bg-emerald-50 p-3 text-emerald-800">{{ session('status') }}</p>@endif
    <div class="mt-5 rounded-2xl border bg-white p-6">
        <p><strong>الحالة:</strong> {{ $serviceRequest->status }}</p>
        <p class="mt-2"><strong>الخدمة:</strong> {{ $serviceRequest->category->name_ar }}</p>
        <p class="mt-2"><strong>الوصف:</strong> {{ $serviceRequest->description }}</p>
        @if(auth()->user()->is_admin || auth()->id() === $serviceRequest->customer_id || $serviceRequest->technician?->user_id === auth()->id())
            <p class="mt-2"><strong>العنوان الخاص:</strong> {{ $serviceRequest->private_address }}</p>
        @else
            <p class="mt-2 rounded-lg bg-amber-50 p-3 text-amber-800">العنوان التفصيلي سيظهر بعد قبول عرض السعر.</p>
        @endif
        @if($serviceRequest->scheduled_at)<p class="mt-2"><strong>الموعد:</strong> {{ $serviceRequest->scheduled_at->format('Y-m-d H:i') }}</p>@endif
        <div class="mt-4 flex flex-wrap gap-3">@foreach($serviceRequest->images as $image)<a class="text-amber-700 underline" href="{{ route('services.images.show', $image) }}" target="_blank" rel="noopener">عرض صورة الطلب</a>@endforeach</div>
    </div>
    @if(auth()->id() === $serviceRequest->customer_id)
        <h2 class="mt-8 text-xl font-bold">عروض الفنيين</h2>
        @forelse($serviceRequest->quotes as $quote)
            <div class="mt-3 rounded-2xl border bg-white p-5">
                <p class="font-bold">{{ $quote->technician->display_name }} — {{ number_format((float)$quote->amount, 2) }} جنيه</p>
                <p class="mt-2 text-slate-600">{{ $quote->details }}</p>
                @if($quote->status === 'pending' && $serviceRequest->status === 'pending')
                <form method="POST" action="{{ route('services.quotes.accept', [$serviceRequest, $quote]) }}" class="mt-3">@csrf<button class="rounded-xl bg-amber-500 px-5 py-2 font-bold">قبول العرض</button></form>
                @endif
            </div>
        @empty<p class="mt-3 text-slate-500">لم تصل عروض أسعار بعد.</p>@endforelse
        @if($serviceRequest->status === 'completed' && !$serviceRequest->review)
            <form method="POST" action="{{ route('services.reviews.store', $serviceRequest) }}" class="mt-8 space-y-3 rounded-2xl border bg-white p-5">@csrf
                <h2 class="font-bold">قيّم الخدمة</h2><select name="rating" required class="w-full rounded-xl border"><option value="">اختر التقييم</option>@for($i=5;$i>=1;$i--)<option value="{{ $i }}">{{ $i }} من 5</option>@endfor</select>
                <textarea name="comment" maxlength="2000" class="w-full rounded-xl border p-3" placeholder="اكتب تعليقك (اختياري)"></textarea><button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white">إرسال التقييم</button>
            </form>
        @endif
    @else
        @if($serviceRequest->status === 'accepted' || $serviceRequest->status === 'scheduled')
            <form method="POST" action="{{ route('services.requests.schedule', $serviceRequest) }}" class="mt-6 rounded-2xl border bg-white p-5">@csrf
                <label class="mb-2 block font-bold">تحديد موعد الزيارة</label><input type="datetime-local" name="scheduled_at" required class="w-full rounded-xl border p-3"><button class="mt-3 rounded-xl bg-amber-500 px-5 py-3 font-bold">حفظ الموعد</button>
            </form>
            <form method="POST" action="{{ route('services.requests.complete', $serviceRequest) }}" class="mt-3">@csrf<button class="rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white">تأكيد إتمام الخدمة</button></form>
        @elseif($serviceRequest->status === 'pending')
            <form method="POST" action="{{ route('services.quotes.store', $serviceRequest) }}" class="mt-6 space-y-3 rounded-2xl border bg-white p-5">@csrf
                <h2 class="font-bold">إرسال عرض سعر</h2><input type="number" name="amount" min="1" step="0.01" required placeholder="السعر بالجنيه" class="w-full rounded-xl border p-3">
                <input type="number" name="estimated_duration_hours" min="1" max="720" placeholder="مدة التنفيذ بالساعات (اختياري)" class="w-full rounded-xl border p-3">
                <textarea name="details" maxlength="2000" placeholder="تفاصيل العرض" class="w-full rounded-xl border p-3"></textarea><button class="rounded-xl bg-amber-500 px-5 py-3 font-bold">إرسال العرض</button>
            </form>
        @endif
    @endif
</section>
</x-layouts.app>
