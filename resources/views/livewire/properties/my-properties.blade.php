<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">عقاراتي</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">
                أدر العقارات التي أضفتها: عدّل بياناتها، انسخها، أرشفها أو احذفها. لا يظهر للعامة إلا ما تعتمده الإدارة.
            </p>
        </div>

        <a href="{{ route('account.properties.create') }}" class="btn-primary shrink-0">
            <x-heroicon-o-plus class="h-4 w-4" />
            إضافة عقار
        </a>
    </div>

    @if ($actionMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $actionMessage }}
        </div>
    @endif

    @if ($actionError)
        <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
            {{ $actionError }}
        </div>
    @endif

    <section class="card-surface p-4 sm:p-5">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="my-properties-search" class="mb-1.5 block text-xs font-bold text-slate-600">بحث</label>
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input id="my-properties-search"
                           type="search"
                           wire:model.live.debounce.400ms="search"
                           placeholder="ابحث بالعنوان أو العنوان التفصيلي..."
                           class="input-field ps-9!">
                </div>
            </div>

            <div>
                <label for="my-properties-status" class="mb-1.5 block text-xs font-bold text-slate-600">الحالة</label>
                <select id="my-properties-status" wire:model.live="statusFilter" class="select-field">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="my-properties-purpose" class="mb-1.5 block text-xs font-bold text-slate-600">الغرض</label>
                <select id="my-properties-purpose" wire:model.live="purpose" class="select-field">
                    <option value="">كل الأغراض</option>
                    @foreach ($purposes as $purposeOption)
                        <option value="{{ $purposeOption->value }}">{{ $purposeOption->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="my-properties-type" class="mb-1.5 block text-xs font-bold text-slate-600">نوع العقار</label>
                <select id="my-properties-type" wire:model.live="type" class="select-field">
                    <option value="">كل الأنواع</option>
                    @foreach ($propertyTypes as $typeOption)
                        <option value="{{ $typeOption->slug }}">{{ $typeOption->name_ar }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($search !== '' || $statusFilter !== '' || $purpose !== '' || $type !== '')
            <div class="mt-3 flex justify-end">
                <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 transition hover:text-brand-900">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                    مسح الفلاتر
                </button>
            </div>
        @endif
    </section>

    <div class="relative"
         wire:loading.class="opacity-60"
         wire:target="search,statusFilter,purpose,type,resetFilters,duplicate,archive,reactivate,markSold,markRented,delete,gotoPage,nextPage,previousPage">
        <div wire:loading.delay
             wire:target="search,statusFilter,purpose,type,resetFilters,duplicate,archive,reactivate,markSold,markRented,delete,gotoPage,nextPage,previousPage"
             class="absolute inset-x-0 top-0 z-10 flex justify-center py-6">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-700 px-4 py-2 text-xs font-bold text-white shadow-soft">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                جارٍ التحديث...
            </span>
        </div>

        @if ($properties->isEmpty())
            <div class="card-surface flex flex-col items-center justify-center px-6 py-16 text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                    <x-heroicon-o-home-modern class="h-8 w-8" />
                </span>

                @if ($search !== '' || $statusFilter !== '' || $purpose !== '' || $type !== '')
                    <h2 class="mt-5 text-lg font-extrabold text-slate-900">لا توجد عقارات مطابقة</h2>
                    <p class="mt-2 max-w-md text-sm leading-7 text-slate-500">جرّب تعديل كلمات البحث أو الفلاتر للعثور على عقاراتك.</p>
                    <button type="button" wire:click="resetFilters" class="btn-primary mt-6">
                        <x-heroicon-o-arrow-path class="h-4 w-4" />
                        مسح الفلاتر
                    </button>
                @else
                    <h2 class="mt-5 text-lg font-extrabold text-slate-900">لم تُضف أي عقارات بعد</h2>
                    <p class="mt-2 max-w-md text-sm leading-7 text-slate-500">ابدأ بإضافة عقارك الأول، واحفظه كمسودة ثم أرسله للمراجعة.</p>
                    <a href="{{ route('account.properties.create') }}" class="btn-primary mt-6">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        إضافة عقار
                    </a>
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach ($properties as $property)
                    <article class="card-surface overflow-hidden" wire:key="my-property-{{ $property->id }}">
                        <div class="flex flex-col sm:flex-row">
                            <div class="shrink-0 sm:w-48">
                                <img src="{{ $property->image_url }}"
                                     alt="{{ $property->title }}"
                                     class="h-44 w-full object-cover sm:h-full">
                            </div>

                            <div class="min-w-0 flex-1 p-4 sm:p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="truncate text-base font-extrabold text-slate-900">{{ $property->title }}</h2>
                                            <x-ui.status-badge :label="$property->status->label()" :color="$property->status->color()" />
                                        </div>

                                        <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-slate-500">
                                            <span>{{ $property->type?->name_ar ?? '—' }}</span>
                                            <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ $property->purpose->label() }}</span>
                                            <span>{{ $property->location_label ?: '—' }}</span>
                                        </p>
                                    </div>

                                    <p class="shrink-0 text-lg font-extrabold text-brand-800">
                                        {{ $property->price_formatted }}
                                        <span class="text-xs font-bold text-slate-500">{{ $property->currency }}</span>
                                        @if ($property->price_suffix !== '')
                                            <span class="text-[11px] font-semibold text-slate-400">{{ $property->price_suffix }}</span>
                                        @endif
                                    </p>
                                </div>

                                <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-[11px] font-semibold text-slate-500">
                                    <div>
                                        <dt class="inline">أضيف في:</dt>
                                        <dd class="inline">{{ $property->created_at?->translatedFormat('j F Y') ?? '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="inline">المساحة:</dt>
                                        <dd class="inline">{{ $property->area_formatted }}</dd>
                                    </div>
                                    @if ($property->bedrooms !== null)
                                        <div>
                                            <dt class="inline">غرف:</dt>
                                            <dd class="inline">{{ $property->bedrooms }}</dd>
                                        </div>
                                    @endif
                                    @if ($property->bathrooms !== null)
                                        <div>
                                            <dt class="inline">حمامات:</dt>
                                            <dd class="inline">{{ $property->bathrooms }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                                    <a href="{{ route('account.properties.edit', $property->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-800">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        تعديل
                                    </a>

                                    <button type="button"
                                            wire:click="duplicate({{ $property->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-800">
                                        <x-heroicon-o-document-duplicate class="h-4 w-4" />
                                        نسخ
                                    </button>

                                    @if ($property->status === \App\Enums\PropertyStatus::Archived)
                                        <button type="button"
                                                wire:click="reactivate({{ $property->id }})"
                                                wire:confirm="سيُرسل العقار للمراجعة مرة أخرى. هل تريد المتابعة؟"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                                            إعادة تفعيل
                                        </button>
                                    @else
                                        <button type="button"
                                                wire:click="archive({{ $property->id }})"
                                                wire:confirm="هل تريد أرشفة هذا العقار؟ لن يظهر للعامة بعد ذلك."
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-amber-300 hover:text-amber-800">
                                            <x-heroicon-o-archive-box class="h-4 w-4" />
                                            أرشفة
                                        </button>
                                    @endif

                                    @if ($property->status === \App\Enums\PropertyStatus::Published)
                                        <a href="{{ route('properties.show', $property) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700 transition hover:bg-brand-100">
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                            عرض
                                        </a>

                                        <button type="button"
                                                wire:click="markSold({{ $property->id }})"
                                                wire:confirm="هل تريد تحديد العقار كمباع؟ سيُخفى من القوائم العامة."
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-300">
                                            <x-heroicon-o-check-badge class="h-4 w-4" />
                                            تم البيع
                                        </button>

                                        <button type="button"
                                                wire:click="markRented({{ $property->id }})"
                                                wire:confirm="هل تريد تحديد العقار كمؤجَّر؟ سيُخفى من القوائم العامة."
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-300">
                                            <x-heroicon-o-key class="h-4 w-4" />
                                            تم التأجير
                                        </button>
                                    @endif

                                    <button type="button"
                                            wire:click="delete({{ $property->id }})"
                                            wire:confirm="هل أنت متأكد من حذف هذا العقار؟ سيُحذف من قائمتك ولن يظهر للعامة."
                                            class="ms-auto inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                        حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
</div>
