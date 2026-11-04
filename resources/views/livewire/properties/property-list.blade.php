<div>
    <section class="relative overflow-hidden bg-brand-950 py-12 text-white sm:py-16">
        <div class="absolute inset-0 opacity-25"
             style="background-image: url('{{ asset('images/hero-city.svg') }}'); background-size: cover; background-position: center;"></div>
        <div class="absolute inset-0 bg-gradient-to-l from-brand-950 via-brand-950/85 to-brand-900/70"></div>

        <div class="container-aqari relative">
            <nav aria-label="مسار التنقل" class="flex items-center gap-2 text-xs font-semibold text-brand-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('app.nav.home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">{{ __('app.nav.properties') }}</span>
            </nav>

            <div class="mt-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('app.listing.title') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-brand-100">{{ __('app.listing.subtitle') }}</p>
                </div>

                <p class="inline-flex w-fit items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-bold text-white ring-1 ring-white/15">
                    <x-heroicon-o-home-modern class="h-4 w-4 text-amber-300" />
                    {{ __('app.listing.results', ['count' => number_format($properties->total())]) }}
                </p>
            </div>
        </div>
    </section>

    <section class="container-aqari py-10">
        <div class="grid gap-8 lg:grid-cols-[290px_minmax(0,1fr)]">
            <aside x-data="{ filtersOpen: false }">
                <button type="button"
                        class="mb-4 inline-flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm lg:hidden"
                        x-on:click="filtersOpen = ! filtersOpen"
                        x-bind:aria-expanded="filtersOpen.toString()"
                        aria-controls="filters-panel">
                    <span class="inline-flex items-center gap-2">
                        <x-heroicon-o-adjustments-horizontal class="h-4 w-4 text-brand-700" />
                        {{ __('app.listing.filters') }}
                    </span>
                    <x-heroicon-o-chevron-down class="h-4 w-4 transition" x-bind:class="filtersOpen ? 'rotate-180' : ''" />
                </button>

                <div id="filters-panel"
                     x-show="filtersOpen"
                     x-cloak
                     class="card-surface p-5 lg:sticky lg:top-24 lg:block!"
                     x-transition.opacity>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                            <x-heroicon-o-adjustments-horizontal class="h-4 w-4 text-brand-700" />
                            {{ __('app.listing.filters') }}
                        </h2>

                        @if ($search !== '' || $purpose !== '' || $type !== '' || $governorate !== '' || $sort !== 'latest')
                            <button type="button" wire:click="resetFilters"
                                    class="text-[11px] font-bold text-brand-700 transition hover:text-brand-900">
                                {{ __('app.actions.reset') }}
                            </button>
                        @endif
                    </div>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label for="filter-search" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.listing.search_label') }}
                            </label>
                            <div class="relative">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input id="filter-search"
                                       type="search"
                                       wire:model.live.debounce.500ms="search"
                                       placeholder="{{ __('app.listing.search_placeholder') }}"
                                       class="input-field ps-9!">
                            </div>
                        </div>

                        <div>
                            <label for="filter-purpose" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.listing.purpose') }}
                            </label>
                            <select id="filter-purpose" wire:model.live="purpose" class="select-field">
                                <option value="">{{ __('app.listing.all') }}</option>
                                @foreach ($purposes as $purposeOption)
                                    <option value="{{ $purposeOption->value }}">{{ $purposeOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-type" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.listing.type') }}
                            </label>
                            <select id="filter-type" wire:model.live="type" class="select-field">
                                <option value="">{{ __('app.listing.all') }}</option>
                                @foreach ($propertyTypes as $typeOption)
                                    <option value="{{ $typeOption->slug }}">{{ $typeOption->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-governorate" class="mb-1.5 block text-xs font-bold text-slate-600">
                                {{ __('app.listing.governorate') }}
                            </label>
                            <select id="filter-governorate" wire:model.live="governorate" class="select-field">
                                <option value="">{{ __('app.listing.all') }}</option>
                                @foreach ($governorates as $governorateOption)
                                    <option value="{{ $governorateOption->slug }}">{{ $governorateOption->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-xl bg-brand-50 p-4">
                            <p class="flex items-start gap-2 text-[11px] leading-6 text-brand-900">
                                <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                <span>كل العقارات المعروضة هنا مراجعة من فريق الإمداد العقارية. تواصل معنا إذا احتجت مساعدة في الاختيار.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-slate-500">
                        {{ __('app.listing.results', ['count' => number_format($properties->total())]) }}
                    </p>

                    <div class="flex items-center gap-2">
                        <label for="sort" class="text-xs font-bold text-slate-600">{{ __('app.listing.sort') }}</label>
                        <select id="sort" wire:model.live="sort" class="select-field w-auto! py-2! pe-9! ps-3! text-xs">
                            <option value="latest">{{ __('app.listing.sort_latest') }}</option>
                            <option value="price_asc">{{ __('app.listing.sort_price_asc') }}</option>
                            <option value="price_desc">{{ __('app.listing.sort_price_desc') }}</option>
                            <option value="area_desc">{{ __('app.listing.sort_area_desc') }}</option>
                        </select>
                    </div>
                </div>

                <div class="relative" wire:loading.class="opacity-50" wire:target="search, purpose, type, governorate, sort, resetFilters, gotoPage, nextPage, previousPage">
                    <div wire:loading.delay
                         wire:target="search, purpose, type, governorate, sort, resetFilters, gotoPage, nextPage, previousPage"
                         class="absolute inset-x-0 top-0 z-10 flex justify-center py-6">
                        <span class="inline-flex items-center gap-2 rounded-full bg-brand-700 px-4 py-2 text-xs font-bold text-white shadow-soft">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            جارٍ تحديث النتائج...
                        </span>
                    </div>

                    @if ($properties->isEmpty())
                        <div class="card-surface flex flex-col items-center justify-center px-6 py-16 text-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                                <x-heroicon-o-magnifying-glass class="h-8 w-8" />
                            </span>
                            <h2 class="mt-5 text-lg font-extrabold text-slate-900">{{ __('app.listing.empty_title') }}</h2>
                            <p class="mt-2 max-w-md text-sm leading-7 text-slate-500">{{ __('app.listing.empty_text') }}</p>
                            <button type="button" wire:click="resetFilters" class="btn-primary mt-6">
                                <x-heroicon-o-arrow-path class="h-4 w-4" />
                                {{ __('app.actions.reset') }}
                            </button>
                        </div>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($properties as $property)
                                <x-property-card :property="$property" wire:key="property-{{ $property->id }}" />
                            @endforeach
                        </div>

                        <div class="mt-10">
                            {{ $properties->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
