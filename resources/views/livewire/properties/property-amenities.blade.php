<div class="card-surface space-y-5 p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-lg font-extrabold text-slate-900">مرافق العقار</h2>
            <p class="mt-1 text-sm leading-7 text-slate-500">
                اختر المرافق المتاحة داخل العقار أو المبنى. تظهر المرافق للعامة بعد نشر العقار فقط.
            </p>
        </div>

        <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">
            {{ count($selected) }} محدد
        </span>
    </div>

    @if ($message)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $message }}
        </div>
    @endif

    @if ($error)
        <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
            {{ $error }}
        </div>
    @endif

    @if (! $canManage)
        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold leading-6 text-amber-900" role="status">
            <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0" />
            <span>لا يمكن تعديل مرافق عقار تم بيعه أو تأجيره. تواصل مع الإدارة إذا كنت تحتاج إلى تحديثه.</span>
        </div>
    @endif

    @if ($amenitiesByCategory->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-10 text-center">
            <x-heroicon-o-sparkles class="mx-auto h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-extrabold text-slate-700">لا توجد مرافق متاحة حاليًا.</p>
            <p class="mt-1 text-xs font-semibold text-slate-500">ستظهر المرافق هنا بعد إضافتها من الإدارة.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($categories as $category)
                @if ($amenitiesByCategory->has($category->value))
                    <fieldset>
                        <legend class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                            <x-dynamic-component :component="$category->icon()" class="h-4 w-4 text-brand-600" />
                            {{ $category->label() }}
                        </legend>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($amenitiesByCategory[$category->value] as $amenity)
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50/40">
                                    <input type="checkbox"
                                           wire:model="selected"
                                           value="{{ $amenity->id }}"
                                           @disabled(! $canManage)
                                           class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 disabled:opacity-50">
                                    <x-dynamic-component :component="$amenity->icon ?: $category->icon()" class="h-4 w-4 shrink-0 text-brand-600" />
                                    <span>{{ $amenity->name_ar }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
            @endforeach
        </div>

        @if ($canManage)
            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="btn-primary">
                    <span wire:loading.remove wire:target="save">حفظ المرافق</span>
                    <span wire:loading wire:target="save">جارٍ الحفظ...</span>
                </button>
            </div>
        @endif
    @endif
</div>
