<x-layouts.app :title="__('app.brand').' | '.__('app.tagline')">
    <section class="relative isolate overflow-hidden bg-brand-950">
        <img src="{{ asset('images/hero-city.svg') }}"
             alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-45"
             fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-l from-brand-950 via-brand-950/90 to-brand-900/55"></div>
        <div class="absolute -start-24 top-10 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>

        <div class="container-aqari relative py-14 sm:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="animate-fade-up text-white">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-bold text-brand-100 ring-1 ring-white/15">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        {{ __('app.home.hero.eyebrow') }}
                    </span>

                    <h1 class="mt-5 text-4xl font-extrabold leading-[1.2] tracking-tight sm:text-5xl">
                        {{ __('app.home.hero.title') }}
                        <span class="relative inline-block text-amber-300">
                            {{ __('app.home.hero.title_highlight') }}
                            <span class="absolute inset-x-0 -bottom-1 h-2 rounded-full bg-amber-400/30" aria-hidden="true"></span>
                        </span>
                    </h1>

                    <p class="mt-5 max-w-xl text-sm leading-8 text-brand-100 sm:text-base">
                        {{ __('app.home.hero.subtitle') }}
                    </p>

                    <ul class="mt-7 grid gap-3 text-sm text-brand-50 sm:grid-cols-2">
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-amber-300" />
                            إعلانات مراجعة قبل النشر
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-amber-300" />
                            تفاصيل وصور واضحة
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-amber-300" />
                            تغطية كل المحافظات
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-amber-300" />
                            بحث سريع وسهل
                        </li>
                    </ul>
                </div>

                <div class="card-surface animate-fade-up p-5 sm:p-6" style="animation-delay: 120ms;">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-extrabold text-slate-900">ابدأ بحثك الآن</h2>
                        <span class="badge bg-brand-50 text-brand-700">
                            <x-heroicon-o-bolt class="h-3.5 w-3.5" />
                            سريع
                        </span>
                    </div>

                    <form action="{{ route('properties.index') }}" method="GET" class="mt-5 space-y-4">
                        <div>
                            <label for="hero-search" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.listing.search_label') }}
                            </label>
                            <div class="relative">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input id="hero-search"
                                       name="q"
                                       type="search"
                                       value="{{ request('q') }}"
                                       placeholder="{{ __('app.home.hero.search_placeholder') }}"
                                       class="input-field ps-9!">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="hero-purpose" class="mb-1.5 block text-xs font-bold text-slate-600">
                                    {{ __('app.home.hero.purpose_label') }}
                                </label>
                                <select id="hero-purpose" name="purpose" class="select-field">
                                    <option value="">{{ __('app.home.hero.any') }}</option>
                                    <option value="sale" @selected(request('purpose') === 'sale')>{{ __('app.property.sale') }}</option>
                                    <option value="rent" @selected(request('purpose') === 'rent')>{{ __('app.property.rent') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="hero-type" class="mb-1.5 block text-xs font-bold text-slate-600">
                                    {{ __('app.home.hero.type_label') }}
                                </label>
                                <select id="hero-type" name="type" class="select-field">
                                    <option value="">{{ __('app.home.hero.any') }}</option>
                                    @foreach ($propertyTypes as $type)
                                        <option value="{{ $type->slug }}" @selected(request('type') === $type->slug)>
                                            {{ $type->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="hero-governorate" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.home.hero.governorate_label') }}
                            </label>
                            <select id="hero-governorate" name="governorate" class="select-field">
                                <option value="">{{ __('app.home.hero.any') }}</option>
                                @foreach ($governorateOptions as $governorate)
                                    <option value="{{ $governorate->slug }}" @selected(request('governorate') === $governorate->slug)>
                                        {{ $governorate->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                            {{ __('app.actions.search') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="container-aqari relative z-10 -mt-8">
        <div class="grid gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 shadow-soft sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-4 bg-white px-5 py-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <x-heroicon-o-home-modern class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['properties']) }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ __('app.home.stats.properties') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white px-5 py-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <x-heroicon-o-map-pin class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['governorates']) }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ __('app.home.stats.governorates') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white px-5 py-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <x-heroicon-o-square-3-stack-3d class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['types']) }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ __('app.home.stats.types') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white px-5 py-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">24/7</p>
                    <p class="text-xs font-semibold text-slate-500">{{ __('app.home.stats.support') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container-aqari py-16 sm:py-20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="section-eyebrow">{{ __('app.home.types.eyebrow') }}</span>
                <h2 class="section-title">{{ __('app.home.types.title') }}</h2>
                <p class="section-subtitle">{{ __('app.home.types.subtitle') }}</p>
            </div>

            <a href="{{ route('properties.index') }}" class="btn-outline w-fit">
                {{ __('app.actions.view_all') }}
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
        </div>

        <div class="mt-9 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($propertyTypes as $type)
                <a href="{{ route('properties.index', ['type' => $type->slug]) }}"
                   class="group card-surface flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-soft sm:p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white">
                        <x-dynamic-component :component="$type->iconComponent()" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold text-slate-900">{{ $type->name_ar }}</span>
                        <span class="mt-0.5 block text-[11px] font-semibold text-slate-500">
                            {{ number_format($type->properties_count) }} {{ __('app.listing.unit') }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white py-16 sm:py-20">
        <div class="container-aqari">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="section-eyebrow">{{ __('app.home.featured.eyebrow') }}</span>
                    <h2 class="section-title">{{ __('app.home.featured.title') }}</h2>
                    <p class="section-subtitle">{{ __('app.home.featured.subtitle') }}</p>
                </div>

                <a href="{{ route('properties.index') }}" class="btn-outline w-fit">
                    {{ __('app.actions.browse_properties') }}
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                </a>
            </div>

            @if ($featuredProperties->isEmpty())
                <div class="mt-9 rounded-2xl border border-dashed border-slate-300 bg-sand-50 px-6 py-14 text-center">
                    <p class="text-sm font-semibold text-slate-600">{{ __('app.home.featured.empty') }}</p>
                    <a href="{{ route('properties.index') }}" class="btn-primary mt-5">{{ __('app.actions.browse_properties') }}</a>
                </div>
            @else
                <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProperties as $property)
                        <x-property-card :property="$property" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="governorates" class="container-aqari py-16 sm:py-20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="section-eyebrow">{{ __('app.home.governorates.eyebrow') }}</span>
                <h2 class="section-title">{{ __('app.home.governorates.title') }}</h2>
                <p class="section-subtitle">{{ __('app.home.governorates.subtitle') }}</p>
            </div>

            <a href="{{ route('properties.index') }}" class="btn-outline w-fit">
                {{ __('app.actions.view_all') }}
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
        </div>

        <div class="mt-9 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($governorates as $governorate)
                <a href="{{ route('properties.index', ['governorate' => $governorate->slug]) }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-soft">
                    <span class="absolute -end-4 -top-4 h-16 w-16 rounded-full bg-brand-50 transition group-hover:bg-brand-100" aria-hidden="true"></span>
                    <div class="relative flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <x-heroicon-o-map-pin class="h-5 w-5" />
                        </span>
                        <x-heroicon-o-arrow-up-left class="h-4 w-4 text-slate-300 transition group-hover:text-brand-600" />
                    </div>
                    <p class="relative mt-4 text-sm font-extrabold text-slate-900">{{ $governorate->name_ar }}</p>
                    <p class="relative mt-1 text-[11px] font-semibold text-slate-500">
                        {{ __('app.listing.results', ['count' => number_format($governorate->properties_count)]) }}
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    <section id="how-it-works" class="border-y border-slate-200 bg-white py-16 sm:py-20">
        <div class="container-aqari">
            <div class="mx-auto max-w-2xl text-center">
                <span class="section-eyebrow">{{ __('app.home.how.eyebrow') }}</span>
                <h2 class="section-title">{{ __('app.home.how.title') }}</h2>
                <p class="section-subtitle mx-auto">{{ __('app.home.how.subtitle') }}</p>
            </div>

            <ol class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'heroicon-o-magnifying-glass', 'title' => __('app.home.how.step1_title'), 'text' => __('app.home.how.step1_text')],
                    ['icon' => 'heroicon-o-document-magnifying-glass', 'title' => __('app.home.how.step2_title'), 'text' => __('app.home.how.step2_text')],
                    ['icon' => 'heroicon-o-chat-bubble-left-right', 'title' => __('app.home.how.step3_title'), 'text' => __('app.home.how.step3_text')],
                ] as $index => $step)
                    <li class="card-surface relative p-6">
                        <span class="absolute -top-3 start-6 inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-700 text-xs font-extrabold text-white shadow-sm">
                            {{ $index + 1 }}
                        </span>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <x-dynamic-component :component="$step['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-base font-extrabold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="container-aqari py-16 sm:py-20">
        <div class="relative overflow-hidden rounded-3xl bg-brand-950 px-6 py-12 text-white sm:px-12">
            <div class="absolute -end-16 -top-16 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -bottom-20 start-1/3 h-56 w-56 rounded-full bg-amber-400/15 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-extrabold sm:text-3xl">{{ __('app.home.cta.title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-brand-100">{{ __('app.home.cta.subtitle') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="mailto:{{ __('app.footer.email') }}" class="btn-accent">
                        <x-heroicon-o-envelope class="h-4 w-4" />
                        {{ __('app.actions.contact_us') }}
                    </a>
                    <a href="{{ route('properties.index') }}" class="btn-outline border-white/20! bg-white/10! text-white! hover:bg-white/20!">
                        {{ __('app.actions.browse_properties') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
