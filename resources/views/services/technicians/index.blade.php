<x-layouts.app>
    <section class="mx-auto max-w-7xl px-4 py-12" dir="rtl">
        <div class="mb-8">
            <p class="text-sm font-semibold text-amber-600">خدمات الإمداد</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">فنيون موثوقون لصيانة منزلك</h1>
            <p class="mt-2 text-slate-600">اختار التخصص وشوف الفنيين المعتمدين في منطقتك.</p>
        </div>
        <form method="GET" class="mb-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-3">
            <select name="category" class="rounded-xl border-slate-300"><option value="">كل التخصصات</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name_ar }}</option>@foreach($category->children as $child)<option value="{{ $child->id }}" @selected(request('category') == $child->id)>— {{ $child->name_ar }}</option>@endforeach @endforeach</select>
            <select name="governorate" class="rounded-xl border-slate-300"><option value="">كل المحافظات</option>@foreach(\App\Models\Governorate::where('is_active', true)->orderBy('name_ar')->get() as $gov)<option value="{{ $gov->id }}" @selected(request('governorate') == $gov->id)>{{ $gov->name_ar }}</option>@endforeach</select>
            <button class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">بحث عن فني</button>
        </form>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse($technicians as $technician)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between"><div><h2 class="text-xl font-bold text-slate-900">{{ $technician->display_name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $technician->professional_title ?: 'فني صيانة منزلية' }}</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">فني معتمد</span></div>
                    <p class="mt-4 line-clamp-3 text-sm leading-7 text-slate-600">{{ $technician->bio }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">@foreach($technician->specializations as $spec)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">{{ $spec->category->name_ar }}</span>@endforeach</div>
                    <div class="mt-5 flex items-center justify-between text-sm text-slate-500"><span>{{ $technician->years_experience }} سنة خبرة</span><span>★ {{ number_format($technician->averageRating(), 1) }}</span></div>
                    <a href="{{ route('services.technicians.show', $technician) }}" class="mt-5 block rounded-xl bg-amber-500 px-4 py-3 text-center font-bold text-slate-950">عرض الملف الشخصي</a>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-10 text-center text-slate-600 md:col-span-3">لا يوجد فنيون معتمدون مطابقون للبحث حاليًا.</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $technicians->links() }}</div>
    </section>
</x-layouts.app>
