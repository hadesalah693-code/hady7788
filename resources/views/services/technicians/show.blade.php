<x-layouts.app>
    <section class="mx-auto max-w-4xl px-4 py-12" dir="rtl">
        <a href="{{ route('services.index') }}" class="text-sm text-amber-700">← رجوع لقائمة الفنيين</a>
        <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">فني معتمد من الإمداد</span>
            <h1 class="mt-4 text-3xl font-bold text-slate-900">{{ $technician->display_name }}</h1>
            <p class="mt-2 text-slate-600">{{ $technician->professional_title }}</p>
            <p class="mt-6 leading-8 text-slate-700">{{ $technician->bio }}</p>
            <p class="mt-4 text-sm text-slate-500">{{ $technician->years_experience }} سنة خبرة · متوسط التقييم {{ number_format($technician->averageRating(), 1) }} / 5</p>
            <h2 class="mt-8 text-lg font-bold">التخصصات</h2>
            <div class="mt-3 flex flex-wrap gap-2">@foreach($technician->specializations as $spec)<span class="rounded-full bg-slate-100 px-4 py-2 text-sm">{{ $spec->category->name_ar }} @if($spec->starting_price) · يبدأ من {{ number_format($spec->starting_price, 0) }} جنيه @else · السعر بالاتفاق @endif</span>@endforeach</div>
            <h2 class="mt-8 text-lg font-bold">مناطق العمل</h2>
            <p class="mt-2 text-slate-600">{{ $technician->serviceAreas->map(fn($area) => $area->governorate?->name_ar)->filter()->unique()->join('، ') }}</p>
            <p class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-600">للحفاظ على خصوصيتك، لا نعرض بيانات التواصل أو العنوان الخاص بالعملاء للعامة. أرسل طلب صيانة من خلال المنصة ليتم التنسيق بأمان.</p>
            <a href="{{ route('services.requests.create') }}" class="mt-6 inline-block rounded-xl bg-amber-500 px-6 py-3 font-bold text-slate-950">اطلب خدمة صيانة</a>
        </div>
        <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-8"><h2 class="text-xl font-bold">تقييمات العملاء</h2>@forelse($technician->reviews as $review)<div class="border-b border-slate-100 py-4"><div class="font-semibold">★ {{ $review->rating }} / 5</div><p class="mt-1 text-slate-600">{{ $review->comment }}</p></div>@empty<p class="mt-4 text-slate-500">لا توجد تقييمات منشورة بعد.</p>@endforelse</div>
    </section>
</x-layouts.app>
