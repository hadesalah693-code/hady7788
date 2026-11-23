<div>
    <section class="relative overflow-hidden bg-brand-950 py-12 text-white sm:py-16">
        <div class="absolute inset-0 opacity-20"
             style="background-image: url('{{ asset('images/hero-city.svg') }}'); background-size: cover; background-position: center;"></div>
        <div class="absolute inset-0 bg-gradient-to-l from-brand-950 via-brand-950/92 to-brand-900/70"></div>
        <div class="hero-grid absolute inset-0 opacity-60" aria-hidden="true"></div>
        <div class="absolute -end-16 -top-10 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>

        <div class="container-aqari relative">
            <nav aria-label="مسار التنقل" class="flex items-center gap-2 text-xs font-semibold text-brand-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('app.nav.home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">{{ __('app.services.title') }}</span>
            </nav>

            <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <span class="eyebrow-light">
                        <x-heroicon-o-wrench-screwdriver class="h-3.5 w-3.5 text-gold-300" />
                        {{ __('app.services.eyebrow') }}
                    </span>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('app.services.title') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-brand-100/90">{{ __('app.services.subtitle') }}</p>

                    <div class="mt-6 flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                            <x-heroicon-o-user-group class="h-4 w-4 text-gold-300" />
                            {{ __('app.services.providers_count', ['count' => number_format($providersCount)]) }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                            <x-heroicon-o-check-badge class="h-4 w-4 text-emerald-300" />
                            {{ __('app.services.completed_count', ['count' => number_format($completedCount)]) }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('services.request') }}" class="btn-accent w-fit px-5! py-3!">
                    <x-heroicon-o-plus class="h-5 w-5" />
                    {{ __('app.services.request_service') }}
                </a>
            </div>
        </div>
    </section>

    <section class="container-aqari py-10">
        <div class="card-surface p-4 sm:p-5">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_220px_auto] md:items-end">
                <div>
                    <label for="service-search" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.search_label') }}</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input id="service-search"
                               type="search"
                               wire:model.live.debounce.400ms="search"
                               placeholder="{{ __('app.services.search_placeholder') }}"
                               class="input-field ps-9!">
                    </div>
                </div>

                <div>
                    <label for="service-category-filter" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.filter_category') }}</label>
                    <select id="service-category-filter" wire:model.live="category" class="select-field">
                        <option value="">{{ __('app.services.all_categories') }}</option>
                        @foreach ($categories as $categoryOption)
                            <option value="{{ $categoryOption->slug }}">{{ $categoryOption->name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="service-governorate-filter" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.filter_governorate') }}</label>
                    <select id="service-governorate-filter" wire:model.live="governorate" class="select-field">
                        <option value="">{{ __('app.services.all_governorates') }}</option>
                        @foreach ($governorates as $governorateOption)
                            <option value="{{ $governorateOption->slug }}">{{ $governorateOption->name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" wire:click="resetFilters"
                        class="btn-outline h-11 justify-center px-4!">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                    {{ __('app.services.reset_filters') }}
                </button>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">{{ __('app.services.categories') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('app.services.categories_hint') }}</p>
                </div>
                @if ($activeCategory !== null)
                    <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-200">{{ $activeCategory->name_ar }}</span>
                @endif
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($categories as $categoryOption)
                    <button type="button"
                            wire:click="$set('category', '{{ $categoryOption->slug }}')"
                            @class([
                                'group flex flex-col items-start gap-3 rounded-2xl border p-4 text-start transition',
                                'border-brand-300 bg-brand-50 ring-1 ring-brand-200' => $category === $categoryOption->slug,
                                'border-slate-200 bg-white hover:border-brand-300 hover:bg-brand-50/60' => $category !== $categoryOption->slug,
                            ])>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-100 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white">
                            <x-dynamic-component :component="$categoryOption->icon ?: 'heroicon-o-wrench-screwdriver'" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-extrabold text-slate-900">{{ $categoryOption->name_ar }}</span>
                        <span class="text-[11px] font-semibold text-slate-500">
                            {{ __('app.services.providers_count', ['count' => number_format($categoryOption->providers_count)]) }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-10">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">{{ __('app.services.providers') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('app.services.providers_hint') }}</p>
                </div>
                <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                    {{ __('app.services.providers_count', ['count' => number_format($providers->total())]) }}
                </span>
            </div>

            @if ($providers->isEmpty())
                <div class="card-surface mt-4 flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                        <x-heroicon-o-user-group class="h-7 w-7" />
                    </span>
                    <h3 class="text-lg font-extrabold text-slate-900">{{ __('app.services.no_providers') }}</h3>
                    <p class="max-w-md text-sm leading-7 text-slate-500">{{ __('app.services.no_providers_hint') }}</p>
                    <a href="{{ route('services.request') }}" class="btn-primary mt-2">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        {{ __('app.services.request_service') }}
                    </a>
                </div>
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($providers as $provider)
                        <article class="card-surface flex flex-col overflow-hidden">
                            <div class="relative h-40 bg-gradient-to-br from-brand-900 to-brand-700">
                                @php($cover = $provider->images->first())
                                @if ($cover !== null)
                                    <img src="{{ $cover->url() }}" alt="{{ $provider->display_name }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-white/80">
                                        <x-heroicon-o-wrench-screwdriver class="h-12 w-12" />
                                    </div>
                                @endif

                                <span class="absolute end-3 top-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $provider->is_available ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $provider->is_available ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $provider->is_available ? __('app.services.available') : __('app.services.unavailable') }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col gap-3 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-extrabold text-slate-900">{{ $provider->display_name }}</h3>
                                        <p class="mt-0.5 flex items-center gap-1 text-[11px] font-semibold text-slate-500">
                                            <x-heroicon-o-map-pin class="h-3.5 w-3.5" />
                                            {{ $provider->governorate?->name_ar ?? '—' }}
                                            @if ($provider->city)
                                                <span aria-hidden="true">·</span>
                                                {{ $provider->city->name_ar }}
                                            @endif
                                        </p>
                                    </div>
                                    <x-ui.stars :rating="$provider->rating_avg" :count="$provider->reviews_count" />
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($provider->categories->take(3) as $categoryOption)
                                        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-100">{{ $categoryOption->name_ar }}</span>
                                    @endforeach
                                </div>

                                @if ($provider->bio)
                                    <p class="line-clamp-2 text-xs leading-6 text-slate-500">{{ $provider->bio }}</p>
                                @endif

                                <div class="mt-auto grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-center">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">{{ number_format($provider->experience_years) }}</p>
                                        <p class="text-[10px] font-semibold text-slate-500">{{ __('app.services.years_experience', ['count' => '']) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">{{ number_format($provider->completed_requests_count) }}</p>
                                        <p class="text-[10px] font-semibold text-slate-500">{{ __('app.services.completed_jobs', ['count' => '']) }}</p>
                                    </div>
                                </div>

                                <a href="{{ route('services.request', ['provider' => $provider->getKey(), 'category' => $provider->categories->first()?->slug]) }}"
                                   class="btn-primary w-full justify-center">
                                    <x-heroicon-o-paper-airplane class="h-4 w-4" />
                                    {{ __('app.services.request_with_provider') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $providers->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
