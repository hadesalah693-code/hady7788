@php
    $gallery = $property->images->isNotEmpty() ? $property->images : collect();
    $firstImage = $gallery->first()?->url ?? $property->image_url;
    $publishedDate = $property->published_at?->translatedFormat('j F Y');
    $currencyLabel = $property->currency_label;
@endphp

<x-layouts.app :title="$property->title.' | '.__('app.brand')" :meta-description="\Illuminate\Support\Str::limit($property->description, 155)">
    @push('head')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Residence',
                'name' => $property->title,
                'description' => $property->description,
                'url' => route('properties.show', $property),
                'image' => $firstImage,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'EG',
                    'addressRegion' => $property->governorate?->name_ar,
                    'addressLocality' => $property->city?->name_ar,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (float) $property->price,
                    'priceCurrency' => $property->currency,
                    'availability' => 'https://schema.org/InStock',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    <div class="border-b border-slate-200 bg-white">
        <div class="container-aqari py-4">
            <nav aria-label="مسار التنقل" class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('home') }}" class="transition hover:text-brand-700">{{ __('app.nav.home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('properties.index') }}" class="transition hover:text-brand-700">{{ __('app.nav.properties') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('properties.index', ['governorate' => $property->governorate?->slug]) }}"
                   class="transition hover:text-brand-700">{{ $property->governorate?->name_ar }}</a>
                <span aria-hidden="true">/</span>
                <span class="max-w-[220px] truncate text-slate-700">{{ $property->title }}</span>
            </nav>
        </div>
    </div>

    <section class="container-aqari py-8 sm:py-10">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge {{ $property->purpose->badgeClasses() }}">{{ $property->purpose_label }}</span>
                    <span class="badge bg-slate-100 text-slate-700">{{ $property->type?->name_ar }}</span>

                    @if ($property->is_featured)
                        <span class="badge bg-amber-100 text-amber-800">
                            <x-heroicon-s-star class="h-3 w-3" />
                            {{ __('app.property.featured') }}
                        </span>
                    @endif

                    @if ($property->is_new)
                        <span class="badge bg-brand-50 text-brand-700">{{ __('app.property.new') }}</span>
                    @endif
                </div>

                <h1 class="mt-3 text-2xl font-extrabold leading-9 text-slate-900 sm:text-3xl">{{ $property->title }}</h1>

                <p class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-o-map-pin class="h-4 w-4 text-brand-600" />
                        {{ $property->location_label }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icons.calendar class="h-4 w-4 text-brand-600" />
                        {{ __('app.property.published') }} {{ $publishedDate }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icons.eye class="h-4 w-4 text-brand-600" />
                        {{ number_format($property->views_count) }} {{ __('app.property.views') }}
                    </span>
                </p>
            </div>

            <div class="shrink-0 rounded-2xl border border-brand-100 bg-brand-50 px-5 py-4 text-start lg:text-end">
                <p class="text-[11px] font-bold text-brand-700">{{ __('app.property.price') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-brand-800">
                    {{ $property->price_formatted }}
                    <span class="text-xs font-semibold text-brand-700">
                        {{ $currencyLabel }}{{ $property->price_suffix }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="min-w-0 space-y-8">
                <div x-data="{ active: @js($firstImage) }" class="card-surface overflow-hidden">
                    <div class="relative aspect-[16/10] bg-slate-100">
                        <img :src="active" src="{{ $firstImage }}" alt="{{ $property->title }}"
                             class="h-full w-full object-cover">

                        @if ($gallery->count() > 1)
                            <span class="absolute bottom-3 end-3 rounded-lg bg-slate-950/70 px-2.5 py-1 text-[11px] font-bold text-white">
                                {{ $gallery->count() }} صور
                            </span>
                        @endif
                    </div>

                    @if ($gallery->count() > 1)
                        <div class="flex gap-3 overflow-x-auto p-3">
                            @foreach ($gallery as $image)
                                <button type="button"
                                        x-on:click="active = @js($image->url)"
                                        class="h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2 border-transparent transition hover:border-brand-400 focus-visible:border-brand-600"
                                        aria-label="عرض الصورة {{ $loop->iteration }}">
                                    <img src="{{ $image->url }}" alt="{{ $image->alt ?? $property->title }}"
                                         loading="lazy" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card-surface p-6">
                    <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.property.details') }}</h2>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-sand-100 px-4 py-3">
                            <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                <x-icons.area class="h-4 w-4 text-brand-600" />
                                {{ __('app.property.area') }}
                            </span>
                            <p class="mt-1 text-sm font-extrabold text-slate-900">{{ $property->area_formatted }}</p>
                        </div>

                        @if ($property->bedrooms)
                            <div class="rounded-xl bg-sand-100 px-4 py-3">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                    <x-icons.bed class="h-4 w-4 text-brand-600" />
                                    {{ __('app.property.bedrooms') }}
                                </span>
                                <p class="mt-1 text-sm font-extrabold text-slate-900">{{ $property->bedrooms }}</p>
                            </div>
                        @endif

                        @if ($property->bathrooms)
                            <div class="rounded-xl bg-sand-100 px-4 py-3">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                    <x-icons.bath class="h-4 w-4 text-brand-600" />
                                    {{ __('app.property.bathrooms') }}
                                </span>
                                <p class="mt-1 text-sm font-extrabold text-slate-900">{{ $property->bathrooms }}</p>
                            </div>
                        @endif

                        @if ($property->floor !== null)
                            <div class="rounded-xl bg-sand-100 px-4 py-3">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                    <x-icons.floor class="h-4 w-4 text-brand-600" />
                                    {{ __('app.property.floor') }}
                                </span>
                                <p class="mt-1 text-sm font-extrabold text-slate-900">
                                    {{ $property->floor === 0 ? 'أرضي' : $property->floor }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <h2 class="mt-8 text-lg font-extrabold text-slate-900">{{ __('app.property.description') }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-8 text-slate-600">{{ $property->description }}</p>
                </div>

                @php
                    $amenityGroups = $property->amenities->groupBy(fn ($amenity) => $amenity->category?->value ?? 'other');
                @endphp

                @if ($property->amenities->isNotEmpty())
                    <div class="card-surface p-6">
                        <h2 class="text-lg font-extrabold text-slate-900">المرافق</h2>

                        <div class="mt-5 space-y-6">
                            @foreach (\App\Enums\AmenityCategory::cases() as $category)
                                @if ($amenityGroups->has($category->value))
                                    <div>
                                        <h3 class="flex items-center gap-2 text-sm font-extrabold text-slate-800">
                                            <x-dynamic-component :component="$category->icon()" class="h-4 w-4 text-brand-600" />
                                            {{ $category->label() }}
                                        </h3>

                                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                            @foreach ($amenityGroups[$category->value] as $amenity)
                                                <div class="flex items-center gap-2 rounded-xl bg-sand-100 px-3 py-2.5 text-sm font-semibold text-slate-700">
                                                    <x-dynamic-component :component="$amenity->icon ?: $category->icon()" class="h-4 w-4 shrink-0 text-brand-600" />
                                                    <span>{{ $amenity->name_ar }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if ($amenityGroups->has('other'))
                                <div>
                                    <h3 class="flex items-center gap-2 text-sm font-extrabold text-slate-800">
                                        <x-heroicon-o-sparkles class="h-4 w-4 text-brand-600" />
                                        مرافق أخرى
                                    </h3>

                                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        @foreach ($amenityGroups['other'] as $amenity)
                                            <div class="flex items-center gap-2 rounded-xl bg-sand-100 px-3 py-2.5 text-sm font-semibold text-slate-700">
                                                <x-heroicon-o-sparkles class="h-4 w-4 shrink-0 text-brand-600" />
                                                <span>{{ $amenity->name_ar }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card-surface p-6">
                    <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.property.location') }}</h2>

                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-bold text-slate-500">{{ __('app.listing.governorate') }}</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $property->governorate?->name_ar }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-bold text-slate-500">المدينة</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $property->city?->name_ar ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-bold text-slate-500">المنطقة</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $property->district?->name_ar ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-bold text-slate-500">نوع العقار</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $property->type?->name_ar }}</dd>
                        </div>
                    </dl>

                    <p class="mt-5 rounded-xl bg-brand-50 px-4 py-3 text-sm leading-7 text-brand-900">
                        يتم تحديد العنوان التفصيلي وموعد المعاينة بالتنسيق مع فريق الإمداد العقارية.
                    </p>
                </div>
            </div>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                <div class="card-surface p-6">
                    <h2 class="text-base font-extrabold text-slate-900">اطلب معاينة أو استفسر عن العقار</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        أرسل طلبك إلى فريق الإمداد. لن تظهر بيانات اتصال المالك، وسيتولى الفريق تنسيق التواصل والمعاينة معك.
                    </p>

                    @if (session('inquiry_status'))
                        <p role="status" class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold leading-6 text-emerald-800">
                            {{ session('inquiry_status') }}
                        </p>
                    @endif

                    @php($currentUser = auth()->user())

                    <form method="POST" action="{{ route('properties.inquiries.store', $property) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden">

                        <div>
                            <label for="inquiry-name" class="block text-sm font-bold text-slate-700">الاسم</label>
                            <input id="inquiry-name" name="name" type="text" required minlength="3" maxlength="120"
                                   value="{{ old('name', $currentUser?->name) }}"
                                   autocomplete="name"
                                   class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="inquiry-phone" class="block text-sm font-bold text-slate-700">رقم الهاتف</label>
                            <input id="inquiry-phone" name="phone" type="tel" required maxlength="20"
                                   value="{{ old('phone', $currentUser?->phone) }}"
                                   autocomplete="tel" dir="ltr"
                                   class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('phone') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="inquiry-email" class="block text-sm font-bold text-slate-700">البريد الإلكتروني <span class="font-normal text-slate-400">(اختياري)</span></label>
                            <input id="inquiry-email" name="email" type="email" maxlength="255"
                                   value="{{ old('email', $currentUser?->email) }}"
                                   autocomplete="email"
                                   class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                            @error('email') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="inquiry-message" class="block text-sm font-bold text-slate-700">تفاصيل الطلب</label>
                            <textarea id="inquiry-message" name="message" rows="4" required minlength="10" maxlength="2000"
                                      placeholder="مثال: أرغب في تحديد موعد لمعاينة العقار..."
                                      class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            إرسال طلب المعاينة
                        </button>
                    </form>

                    <div class="mt-5 flex items-start gap-2 rounded-xl bg-brand-50 px-4 py-3 text-[11px] leading-6 text-brand-900">
                        <x-heroicon-o-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        <span>بياناتك تصل إلى فريق الإمداد لمتابعة الطلب، ولا نعرض بيانات المالك مباشرة على الزوار.</span>
                    </div>
                </div>

                <div class="card-surface p-6">
                    <h2 class="text-sm font-extrabold text-slate-900">ملخص سريع</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">{{ __('app.property.price') }}</span>
                            <span class="font-bold text-brand-700">{{ $property->price_formatted }} {{ $currencyLabel }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">{{ __('app.property.area') }}</span>
                            <span class="font-bold text-slate-800">{{ $property->area_formatted }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">{{ __('app.listing.purpose') }}</span>
                            <span class="font-bold text-slate-800">{{ $property->purpose_label }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">كود العقار</span>
                            <span class="font-bold text-slate-800" dir="ltr">AQ-{{ str_pad((string) $property->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>

    @if ($similarProperties->isNotEmpty())
        <section class="border-t border-slate-200 bg-white py-14">
            <div class="container-aqari">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="section-eyebrow">{{ __('app.property.similar') }}</span>
                        <h2 class="section-title">عقارات قد تناسبك أيضًا</h2>
                    </div>

                    <a href="{{ route('properties.index', ['type' => $property->type?->slug]) }}" class="btn-outline w-fit">
                        {{ __('app.actions.view_all') }}
                        <x-heroicon-o-arrow-left class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($similarProperties as $similar)
                        <x-property-card :property="$similar" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
