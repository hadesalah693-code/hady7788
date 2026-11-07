<x-layouts.app>
<section class="mx-auto max-w-6xl px-4 py-10" dir="rtl">
    <h1 class="text-2xl font-bold">طلبات صيانة متاحة في مناطق عملك</h1>
    <p class="mt-2 text-slate-600">العنوان التفصيلي وبيانات العميل لا تظهر قبل قبول عرضك.</p>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
    @forelse($jobs as $job)
        <article class="rounded-2xl border bg-white p-5">
            <h2 class="text-lg font-bold">{{ $job->title }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ $job->category->name_ar }} · {{ $job->governorate->name_ar }} @if($job->city) · {{ $job->city->name_ar }} @endif</p>
            <p class="mt-3 leading-7">{{ $job->description }}</p>
            <a href="{{ route('services.requests.show', $job) }}" class="mt-4 inline-block rounded-xl bg-amber-500 px-5 py-3 font-bold">التفاصيل وإرسال عرض سعر</a>
        </article>
    @empty
        <p class="rounded-xl bg-slate-50 p-6 text-slate-600 md:col-span-2">لا توجد طلبات متاحة لتخصصاتك ومناطق عملك حاليًا.</p>
    @endforelse
    </div>
    <div class="mt-6">{{ $jobs->links() }}</div>
</section>
</x-layouts.app>