<x-layouts.app>
    <section class="mx-auto max-w-3xl px-4 py-12" dir="rtl">
        <h1 class="text-3xl font-bold text-slate-900">انضم لفنيي الإمداد</h1>
        <p class="mt-2 text-slate-600">قدّم بياناتك وتخصصاتك ومناطق عملك. ملفك لن يظهر للعملاء إلا بعد اعتماد الإدارة.</p>
        <form method="POST" action="{{ route('services.apply.store') }}" class="mt-8 space-y-5 rounded-3xl border border-slate-200 bg-white p-6">@csrf
            <div><label class="mb-2 block font-semibold">الاسم الظاهر للعملاء</label><input name="display_name" required maxlength="150" value="{{ old('display_name', auth()->user()->name) }}" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="mb-2 block font-semibold">المسمى المهني</label><input name="professional_title" maxlength="150" class="w-full rounded-xl border-slate-300" placeholder="فني كهرباء / سباك محترف"></div>
            <div><label class="mb-2 block font-semibold">سنوات الخبرة</label><input name="years_experience" type="number" min="0" max="60" required value="{{ old('years_experience', 0) }}" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="mb-2 block font-semibold">نبذة عن خبرتك</label><textarea name="bio" rows="4" maxlength="3000" class="w-full rounded-xl border-slate-300"></textarea></div>
            <fieldset><legend class="mb-3 font-semibold">اختار تخصصًا أو أكثر</legend><div class="grid gap-3 sm:grid-cols-2">@foreach($categories as $category)<label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3"><input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, old('categories', [])))>{{ $category->name_ar }}</label>@endforeach</div></fieldset>
            <fieldset><legend class="mb-3 font-semibold">المحافظات التي تعمل بها</legend><div class="grid gap-3 sm:grid-cols-2">@foreach($governorates as $gov)<label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3"><input type="checkbox" name="governorates[]" value="{{ $gov->id }}" @checked(in_array($gov->id, old('governorates', [])))>{{ $gov->name_ar }}</label>@endforeach</div></fieldset>
            <button class="w-full rounded-xl bg-amber-500 px-5 py-4 font-bold text-slate-950">إرسال طلب الانضمام</button>
        </form>
    </section>
</x-layouts.app>
