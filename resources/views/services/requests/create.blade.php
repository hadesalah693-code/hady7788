<x-layouts.app>
    <section class="mx-auto max-w-3xl px-4 py-12" dir="rtl">
        <h1 class="text-3xl font-bold text-slate-900">اطلب خدمة صيانة منزلية</h1>
        <p class="mt-2 text-slate-600">اكتب تفاصيل المشكلة وسيتم توجيه الطلب للفنيين المناسبين. عنوانك لا يظهر للعامة.</p>
        <form method="POST" action="{{ route('services.requests.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
            <div><label class="mb-2 block font-semibold">نوع الخدمة</label><select name="service_category_id" required class="w-full rounded-xl border-slate-300">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name_ar }}</option>@endforeach</select>@error('service_category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div class="grid gap-4 md:grid-cols-2"><div><label class="mb-2 block font-semibold">المحافظة</label><select name="governorate_id" required class="w-full rounded-xl border-slate-300">@foreach($governorates as $gov)<option value="{{ $gov->id }}">{{ $gov->name_ar }}</option>@endforeach</select></div><div><label class="mb-2 block font-semibold">رقم المدينة (اختياري)</label><input name="city_id" type="number" min="1" class="w-full rounded-xl border-slate-300"></div></div>
            <div><label class="mb-2 block font-semibold">عنوان مختصر للمشكلة</label><input name="title" required maxlength="180" class="w-full rounded-xl border-slate-300" placeholder="مثلاً: تسريب مياه في المطبخ"></div>
            <div><label class="mb-2 block font-semibold">اشرح المشكلة</label><textarea name="description" required minlength="15" rows="5" class="w-full rounded-xl border-slate-300"></textarea></div>
            <div><label class="mb-2 block font-semibold">العنوان التفصيلي (سري)</label><textarea name="private_address" required minlength="8" rows="3" class="w-full rounded-xl border-slate-300" placeholder="لن يظهر هذا العنوان في نتائج البحث العامة"></textarea></div>
            <div><label class="mb-2 block font-semibold">الوقت المناسب للزيارة</label><input name="preferred_time" maxlength="80" class="w-full rounded-xl border-slate-300" placeholder="مثلاً: بعد الساعة 5 مساءً"></div>
            <div><label class="mb-2 block font-semibold">صور المشكلة (حتى 5 صور)</label><input name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="w-full rounded-xl border border-slate-300 p-3"><p class="mt-1 text-xs text-slate-500">كل صورة حتى 5 ميجابايت.</p></div>
            <button class="w-full rounded-xl bg-amber-500 px-5 py-4 font-bold text-slate-950">إرسال طلب الصيانة</button>
        </form>
    </section>
</x-layouts.app>
