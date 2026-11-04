<div class="card-surface space-y-6 p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-lg font-extrabold text-slate-900">صور العقار</h2>
            <p class="mt-1 text-sm leading-7 text-slate-500">
                أضف صورًا واضحة للعقار. تُحفظ الصور بشكل خاص ولا تظهر للعامة إلا بعد نشر العقار واعتماده.
            </p>
        </div>

        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-200">
            {{ $property->images->count() }} / {{ $maxImages }}
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
            <span>لا يمكن تعديل صور عقار تم بيعه أو تأجيره. تواصل مع الإدارة إذا كنت تحتاج إلى تحديثه.</span>
        </div>
    @endif

    @if ($canManage)
        <div class="rounded-2xl border border-dashed border-brand-300 bg-brand-50/60 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-extrabold text-brand-900">إضافة صور جديدة</p>
                    <p class="mt-1 text-xs font-semibold text-brand-700">
                        JPG أو PNG أو WEBP — بحد أقصى {{ $maxSizeLabel }} للصورة.
                    </p>
                </div>

                <label class="btn-outline cursor-pointer px-4! py-2.5!">
                    <x-heroicon-o-photo class="h-4 w-4" />
                    اختيار الصور
                    <input type="file" wire:model="photos" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
                </label>
            </div>

            <p wire:loading wire:target="photos" class="mt-3 text-xs font-bold text-brand-700">جارٍ تجهيز الصور...</p>

            @if (count($photos) > 0)
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($photos as $index => $photo)
                        <div class="group relative overflow-hidden rounded-xl border border-brand-200 bg-white" wire:key="temp-{{ $index }}-{{ $photo->getFilename() }}">
                            <img src="{{ $photo->temporaryUrl() }}" alt="معاينة الصورة {{ $index + 1 }}" class="aspect-[4/3] w-full object-cover">
                            <button type="button"
                                    wire:click="removePhoto({{ $index }})"
                                    class="absolute end-1.5 top-1.5 rounded-full bg-slate-950/70 p-1 text-white transition hover:bg-rose-600"
                                    aria-label="إزالة الصورة">
                                <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button"
                            wire:click="upload"
                            wire:loading.attr="disabled"
                            wire:target="photos,upload"
                            class="btn-primary">
                        <span wire:loading.remove wire:target="upload">رفع الصور</span>
                        <span wire:loading wire:target="upload">جارٍ الرفع...</span>
                    </button>
                </div>
            @endif
        </div>
    @endif

    @if ($replacingImageId !== null)
        <div class="space-y-3 rounded-2xl border border-brand-200 bg-brand-50 p-4">
            <p class="text-sm font-extrabold text-brand-900">استبدال الصورة</p>
            <p class="text-xs font-semibold text-brand-700">سيتم الحفاظ على ترتيب الصورة وحالة الصورة الرئيسية.</p>

            <input type="file" wire:model="replacement" accept="image/jpeg,image/png,image/webp" class="input-field">
            @error('replacement') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="cancelReplace" class="btn-ghost px-4! py-2.5!">إلغاء</button>
                <button type="button"
                        wire:click="replace"
                        wire:loading.attr="disabled"
                        wire:target="replacement,replace"
                        class="btn-primary">
                    <span wire:loading.remove wire:target="replace">تأكيد الاستبدال</span>
                    <span wire:loading wire:target="replace">جارٍ الاستبدال...</span>
                </button>
            </div>
        </div>
    @endif

    @if ($property->images->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-10 text-center">
            <x-heroicon-o-photo class="mx-auto h-10 w-10 text-slate-300" />
            <p class="mt-3 text-sm font-extrabold text-slate-700">لا توجد صور لهذا العقار بعد.</p>
            <p class="mt-1 text-xs font-semibold text-slate-500">أضف صورة واحدة على الأقل ليظهر العقار بشكل احترافي.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($property->images as $image)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white" wire:key="image-{{ $image->id }}">
                    <div class="relative aspect-[4/3] bg-slate-100">
                        <img src="{{ $image->url }}"
                             alt="{{ $image->alt ?? $property->title }}"
                             loading="lazy"
                             class="h-full w-full object-cover">

                        @if ($image->is_cover)
                            <span class="absolute start-2 top-2 badge bg-emerald-600 text-white">
                                <x-heroicon-s-star class="h-3 w-3" />
                                الصورة الرئيسية
                            </span>
                        @endif

                        <span class="absolute bottom-2 end-2 rounded-lg bg-slate-950/70 px-2 py-1 text-[11px] font-bold text-white">
                            {{ $loop->iteration }}
                        </span>
                    </div>

                    @if ($canManage)
                        <div class="flex flex-wrap gap-1.5 border-t border-slate-100 p-2.5">
                            @unless ($image->is_cover)
                                <button type="button" wire:click="setCover({{ $image->id }})" class="btn-ghost px-2.5! py-1.5! text-[11px]">
                                    <x-heroicon-o-star class="h-3.5 w-3.5" />
                                    رئيسية
                                </button>
                            @endunless

                            <button type="button" wire:click="moveUp({{ $image->id }})" @disabled($loop->first) class="btn-ghost px-2.5! py-1.5! text-[11px] disabled:opacity-40">
                                <x-heroicon-o-chevron-up class="h-3.5 w-3.5" />
                                أعلى
                            </button>

                            <button type="button" wire:click="moveDown({{ $image->id }})" @disabled($loop->last) class="btn-ghost px-2.5! py-1.5! text-[11px] disabled:opacity-40">
                                <x-heroicon-o-chevron-down class="h-3.5 w-3.5" />
                                أسفل
                            </button>

                            <button type="button" wire:click="startReplace({{ $image->id }})" class="btn-ghost px-2.5! py-1.5! text-[11px]">
                                <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                                استبدال
                            </button>

                            <button type="button"
                                    wire:click="deleteImage({{ $image->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذه الصورة؟ لا يمكن التراجع."
                                    class="btn-ghost px-2.5! py-1.5! text-[11px] text-rose-600 hover:bg-rose-50">
                                <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                حذف
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
