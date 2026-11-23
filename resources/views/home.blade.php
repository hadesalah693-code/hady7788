<x-layouts.app :title="__('app.brand').' | '.__('app.tagline')">
    {{-- ============================== HERO ============================== --}}
    <section class="relative isolate overflow-hidden bg-brand-950">
        <img src="{{ asset('images/hero-city.svg') }}"
             alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-40"
             fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-l from-brand-950 via-brand-950/92 to-brand-900/60"></div>
        <div class="hero-grid absolute inset-0 opacity-70" aria-hidden="true"></div>
        <div class="absolute -start-24 top-8 h-80 w-80 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -end-20 bottom-0 h-72 w-72 rounded-full bg-gold-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="container-aqari relative pb-28 pt-14 sm:pb-32 sm:pt-20 lg:pb-36 lg:pt-24">
            <div class="grid items-center gap-12 lg:grid-cols-[1.08fr_0.92fr]">
                <div class="animate-fade-up text-white">
                    <span class="eyebrow-light">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                        {{ __('app.home.hero.eyebrow') }}
                    </span>

                    <h1 class="mt-6 text-[2.1rem] font-extrabold leading-[1.18] tracking-tight text-white sm:text-5xl lg:text-[3.4rem]">
                        {{ __('app.home.hero.title') }}
                        <span class="relative inline-block whitespace-nowrap text-gold-300">
                            {{ __('app.home.hero.title_highlight') }}
                            <span class="absolute inset-x-0 -bottom-1 h-2.5 rounded-full bg-gold-400/25" aria-hidden="true"></span>
                        </span>
                    </h1>

                    <p class="mt-6 max-w-xl text-sm leading-8 text-brand-100/90 sm:text-base">
                        {{ __('app.home.hero.subtitle') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-2.5">
                        @foreach ([
                            __('app.home.hero.trust_reviewed'),
                            __('app.home.hero.trust_details'),
                            __('app.home.hero.trust_coverage'),
                            __('app.home.hero.trust_fast'),
                        ] as $trust)
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.07] px-3.5 py-2 text-xs font-semibold text-brand-50 backdrop-blur">
                                <x-heroicon-s-check-circle class="h-4 w-4 text-gold-400" />
                                {{ $trust }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Trust / social proof panel --}}
                <div class="animate-fade-up" style="animation-delay: 140ms;">
                    <div class="rounded-3xl border border-white/12 bg-white/[0.06] p-6 shadow-glow backdrop-blur-xl sm:p-7">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">تجربة موثوقة</p>
                                <p class="mt-1 text-lg font-extrabold text-white">منصة عقارية بمعايير عالمية</p>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/25">
                                <x-heroicon-o-shield-check class="h-6 w-6" />
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-brand-950/40 px-4 py-3.5">
                                <p class="text-2xl font-extrabold text-white">{{ number_format($stats['properties']) }}</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-brand-200">{{ __('app.home.stats.properties') }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-brand-950/40 px-4 py-3.5">
                                <p class="text-2xl font-extrabold text-white">{{ number_format($stats['governorates']) }}</p>
                                <p class="mt-0.5 text-[11px] font-semibold text-brand-200">{{ __('app.home.stats.governorates') }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-white/10 bg-brand-950/40 px-4 py-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500/20 text-brand-200">
                                <x-heroicon-o-check-badge class="h-5 w-5" />
                            </span>
                            <p class="text-xs font-semibold leading-5 text-brand-100">
                                إعلانات مراجَعة يدويًا وصور حقيقية قبل النشر
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== SEARCH PANEL ============================== --}}
    <section class="container-aqari relative z-20 -mt-20 sm:-mt-24">
        <div class="overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-lift">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-7">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">ابدأ بحثك الآن</h2>
                        <p class="text-[11px] font-semibold text-slate-500">آلاف العقارات في كل المحافظات</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 ring-1 ring-emerald-100">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                    نتائج محدّثة
                </span>
            </div>

            <form action="{{ route('properties.index') }}" method="GET" class="p-5 sm:p-7">
                <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto] lg:items-end">
                    <div>
                        <label for="hero-search" class="field-label">{{ __('app.listing.search_label') }}</label>
                        <div class="relative">
                            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input id="hero-search"
                                   name="q"
                                   type="search"
                                   value="{{ request('q') }}"
                                   placeholder="{{ __('app.home.hero.search_placeholder') }}"
                                   class="input-field ps-10!">
                        </div>
                    </div>

                    <div>
                        <label for="hero-purpose" class="field-label">{{ __('app.home.hero.purpose_label') }}</label>
                        <select id="hero-purpose" name="purpose" class="select-field">
                            <option value="">{{ __('app.home.hero.any') }}</option>
                            <option value="sale" @selected(request('purpose') === 'sale')>{{ __('app.property.sale') }}</option>
                            <option value="rent" @selected(request('purpose') === 'rent')>{{ __('app.property.rent') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="hero-type" class="field-label">{{ __('app.home.hero.type_label') }}</label>
                        <select id="hero-type" name="type" class="select-field">
                            <option value="">{{ __('app.home.hero.any') }}</option>
                            @foreach ($propertyTypes as $type)
                                <option value="{{ $type->slug }}" @selected(request('type') === $type->slug)>
                                    {{ $type->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="hero-governorate" class="field-label">{{ __('app.home.hero.governorate_label') }}</label>
                        <select id="hero-governorate" name="governorate" class="select-field">
                            <option value="">{{ __('app.home.hero.any') }}</option>
                            @foreach ($governorateOptions as $governorate)
                                <option value="{{ $governorate->slug }}" @selected(request('governorate') === $governorate->slug)>
                                    {{ $governorate->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary h-[46px] w-full lg:w-auto lg:px-8!">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                        {{ __('app.actions.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- ============================== STATS ============================== --}}
    <section class="container-aqari mt-8">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-tile">
                <span class="icon-tile">
                    <x-heroicon-o-home-modern class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['properties']) }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('app.home.stats.properties') }}</p>
                </div>
            </div>

            <div class="stat-tile">
                <span class="icon-tile">
                    <x-heroicon-o-map-pin class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['governorates']) }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('app.home.stats.governorates') }}</p>
                </div>
            </div>

            <div class="stat-tile">
                <span class="icon-tile">
                    <x-heroicon-o-square-3-stack-3d class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">{{ number_format($stats['types']) }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('app.home.stats.types') }}</p>
                </div>
            </div>

            <div class="stat-tile">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gold-500/10 text-gold-600">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xl font-extrabold text-slate-900">24/7</p>
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('app.home.stats.support') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== PROPERTY TYPES ============================== --}}
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
                   class="group card-surface relative flex items-center gap-4 overflow-hidden p-4 transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-soft sm:p-5">
                    <span class="absolute -end-6 -top-6 h-16 w-16 rounded-full bg-brand-50/0 transition duration-300 group-hover:bg-brand-50" aria-hidden="true"></span>
                    <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 transition duration-300 group-hover:bg-brand-700 group-hover:text-white group-hover:shadow-[0_12px_24px_-12px_rgba(13,107,89,0.8)]">
                        <x-dynamic-component :component="$type->iconComponent()" class="h-6 w-6" />
                    </span>
                    <span class="relative min-w-0">
                        <span class="block truncate text-sm font-extrabold text-slate-900">{{ $type->name_ar }}</span>
                        <span class="mt-0.5 block text-[11px] font-semibold text-slate-500">
                            {{ number_format($type->properties_count) }} {{ __('app.listing.unit') }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================== FEATURED ============================== --}}
    <section class="border-y border-slate-200/70 bg-white py-16 sm:py-20">
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
                <div class="mt-9 rounded-3xl border border-dashed border-slate-300 bg-sand-50 px-6 py-16 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-700 shadow-card">
                        <x-heroicon-o-home-modern class="h-7 w-7" />
                    </span>
                    <p class="mt-4 text-sm font-semibold text-slate-600">{{ __('app.home.featured.empty') }}</p>
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

    {{-- ============================== GOVERNORATES ============================== --}}
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
                   class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-soft">
                    <span class="absolute -end-5 -top-5 h-16 w-16 rounded-full bg-brand-50 transition duration-300 group-hover:bg-brand-100" aria-hidden="true"></span>
                    <div class="relative flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <x-heroicon-o-map-pin class="h-5 w-5" />
                        </span>
                        <x-heroicon-o-arrow-up-left class="h-4 w-4 text-slate-300 transition duration-300 group-hover:text-brand-600" />
                    </div>
                    <p class="relative mt-4 text-sm font-extrabold text-slate-900">{{ $governorate->name_ar }}</p>
                    <p class="relative mt-1 text-[11px] font-semibold text-slate-500">
                        {{ __('app.listing.results', ['count' => number_format($governorate->properties_count)]) }}
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================== HOW IT WORKS ============================== --}}
    <section id="how-it-works" class="border-y border-slate-200/70 bg-white py-16 sm:py-20">
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
                    <li class="card-surface relative p-6 pt-7 transition duration-300 hover:-translate-y-1 hover:shadow-soft">
                        <span class="absolute -top-3.5 start-6 inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-700 text-xs font-extrabold text-white shadow-[0_10px_20px_-10px_rgba(13,107,89,0.9)]">
                            {{ $index + 1 }}
                        </span>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                            <x-dynamic-component :component="$step['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-base font-extrabold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-500">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============================== CTA ============================== --}}
    <section class="container-aqari py-16 sm:py-20">
        <div class="relative overflow-hidden rounded-[2rem] bg-brand-950 px-6 py-12 text-white shadow-lift sm:px-12 sm:py-14">
            <div class="hero-grid absolute inset-0 opacity-60" aria-hidden="true"></div>
            <div class="absolute -end-16 -top-16 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -bottom-20 start-1/3 h-56 w-56 rounded-full bg-gold-400/15 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col items-start justify-between gap-7 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <span class="eyebrow-light">
                        <x-heroicon-o-sparkles class="h-3.5 w-3.5 text-gold-300" />
                        جاهز تبدأ؟
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold text-white sm:text-3xl">{{ __('app.home.cta.title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-brand-100/90">{{ __('app.home.cta.subtitle') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="mailto:{{ __('app.footer.email') }}" class="btn-accent">
                        <x-heroicon-o-envelope class="h-4 w-4" />
                        {{ __('app.actions.contact_us') }}
                    </a>
                    <a href="{{ route('properties.index') }}" class="btn-outline border-white/20! bg-white/10! text-white! hover:border-white/40! hover:bg-white/20!">
                        {{ __('app.actions.browse_properties') }}
                        <x-heroicon-o-arrow-left class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
