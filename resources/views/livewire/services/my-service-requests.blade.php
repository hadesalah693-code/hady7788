<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.services.my_requests.title') }}</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.services.my_requests.subtitle') }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('services.index') }}" class="btn-outline shrink-0">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                {{ __('app.services.title') }}
            </a>
            <a href="{{ route('services.request') }}" class="btn-primary shrink-0">
                <x-heroicon-o-plus class="h-4 w-4" />
                {{ __('app.services.my_requests.new_request') }}
            </a>
        </div>
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
        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px_auto] sm:items-end">
            <div>
                <label for="my-requests-search" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.search_label') }}</label>
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input id="my-requests-search" type="search" wire:model.live.debounce.400ms="search"
                           placeholder="{{ __('app.services.my_requests.search_placeholder') }}"
                           class="input-field ps-9!">
                </div>
            </div>

            <div>
                <label for="my-requests-status" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.my_requests.status') }}</label>
                <select id="my-requests-status" wire:model.live="statusFilter" class="select-field">
                    <option value="">{{ __('app.services.my_requests.all_statuses') }}</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-200">
                    {{ __('app.services.my_requests.open_requests', ['count' => number_format($openCount)]) }}
                </span>
                <button type="button" wire:click="resetFilters" class="btn-outline h-11 px-3!" aria-label="{{ __('app.services.reset_filters') }}">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                </button>
            </div>
        </div>
    </section>

    @if ($requests->isEmpty())
        <div class="card-surface flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                <x-heroicon-o-clipboard-document-list class="h-7 w-7" />
            </span>
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.services.my_requests.empty') }}</h2>
            <p class="max-w-md text-sm leading-7 text-slate-500">{{ __('app.services.my_requests.empty_hint') }}</p>
            <a href="{{ route('services.request') }}" class="btn-primary mt-2">
                <x-heroicon-o-plus class="h-4 w-4" />
                {{ __('app.services.my_requests.new_request') }}
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($requests as $serviceRequest)
                <article class="card-surface p-4 sm:p-5" wire:key="request-{{ $serviceRequest->getKey() }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200" dir="ltr">{{ $serviceRequest->reference }}</span>
                                <x-ui.status-badge :label="$serviceRequest->status->label()" :color="$serviceRequest->status->color()" />
                                @if ($serviceRequest->review)
                                    <span class="badge bg-amber-50 text-amber-700 ring-1 ring-amber-200">{{ __('app.services.details.your_review') }}</span>
                                @endif
                            </div>

                            <h2 class="mt-2 text-base font-extrabold text-slate-900">
                                <a href="{{ route('account.service-requests.show', $serviceRequest) }}" class="transition hover:text-brand-700">
                                    {{ $serviceRequest->title }}
                                </a>
                            </h2>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-wrench-screwdriver class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->category?->name_ar ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-user class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->provider?->display_name ?? __('app.services.my_requests.not_assigned') }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-map-pin class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->governorate?->name_ar ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->created_at?->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <a href="{{ route('account.service-requests.show', $serviceRequest) }}" class="btn-outline px-3! py-2!">
                                {{ __('app.services.my_requests.view') }}
                            </a>

                            @if ($serviceRequest->status->isOpen())
                                <button type="button"
                                        wire:click="cancel({{ $serviceRequest->getKey() }})"
                                        wire:confirm="{{ __('app.services.my_requests.cancel_confirm') }}"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                    <x-heroicon-o-x-circle class="h-4 w-4" />
                                    {{ __('app.services.my_requests.cancel') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($serviceRequest->preferred_date)
                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600">
                            <x-heroicon-o-calendar-days class="h-3.5 w-3.5 text-brand-600" />
                            {{ __('app.services.my_requests.preferred_date') }}:
                            {{ $serviceRequest->preferred_date->translatedFormat('d M Y') }}
                            @if ($serviceRequest->preferred_time)
                                — {{ $serviceRequest->preferred_time }}
                            @endif
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    @endif
</div>
