<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.services.provider_requests.title') }}</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.services.provider_requests.subtitle') }}</p>
        </div>

        <a href="{{ route('account.service-provider') }}" class="btn-outline shrink-0">
            <x-heroicon-o-user-circle class="h-4 w-4" />
            {{ __('app.services.provider.title') }}
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

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            'assigned' => ['label' => __('app.services.provider_requests.stats.assigned'), 'color' => 'info', 'icon' => 'heroicon-o-user-plus'],
            'accepted' => ['label' => __('app.services.provider_requests.stats.accepted'), 'color' => 'success', 'icon' => 'heroicon-o-check-badge'],
            'in_progress' => ['label' => __('app.services.provider_requests.stats.in_progress'), 'color' => 'warning', 'icon' => 'heroicon-o-wrench-screwdriver'],
            'completed' => ['label' => __('app.services.provider_requests.stats.completed'), 'color' => 'success', 'icon' => 'heroicon-o-check-circle'],
        ] as $statusKey => $meta)
            <div class="card-surface p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-bold text-slate-400">{{ $meta['label'] }}</p>
                    <x-dynamic-component :component="$meta['icon']" class="h-4 w-4 text-brand-600" />
                </div>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format((int) ($counts[$statusKey] ?? 0)) }}</p>
            </div>
        @endforeach
    </div>

    <section class="card-surface p-4 sm:p-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="w-full sm:w-64">
                <label for="provider-requests-status" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.my_requests.status') }}</label>
                <select id="provider-requests-status" wire:model.live="statusFilter" class="select-field">
                    <option value="">{{ __('app.services.provider_requests.all_statuses') }}</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>

            <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                {{ __('app.services.providers_count', ['count' => number_format($requests->total())]) }}
            </span>
        </div>
    </section>

    @if ($requests->isEmpty())
        <div class="card-surface flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                <x-heroicon-o-inbox class="h-7 w-7" />
            </span>
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.services.provider_requests.empty') }}</h2>
            <p class="max-w-md text-sm leading-7 text-slate-500">{{ __('app.services.provider.pending_notice') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($requests as $serviceRequest)
                <article class="card-surface p-4 sm:p-5" wire:key="provider-request-{{ $serviceRequest->getKey() }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200" dir="ltr">{{ $serviceRequest->reference }}</span>
                                <x-ui.status-badge :label="$serviceRequest->status->label()" :color="$serviceRequest->status->color()" />
                            </div>

                            <h2 class="mt-2 text-base font-extrabold text-slate-900">{{ $serviceRequest->title }}</h2>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-wrench-screwdriver class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->category?->name_ar ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-user class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->customer?->name ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-map-pin class="h-3.5 w-3.5" />
                                    {{ $serviceRequest->governorate?->name_ar ?? '—' }}
                                    @if ($serviceRequest->city)
                                        — {{ $serviceRequest->city->name_ar }}
                                    @endif
                                </span>
                                @if ($serviceRequest->preferred_date)
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                                        {{ $serviceRequest->preferred_date->translatedFormat('d M Y') }}
                                        @if ($serviceRequest->preferred_time)
                                            — {{ $serviceRequest->preferred_time }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if ($serviceRequest->status === \App\Enums\ServiceRequestStatus::Assigned)
                                <button type="button" wire:click="accept({{ $serviceRequest->getKey() }})"
                                        wire:confirm="{{ __('app.services.provider_requests.confirm_accept') }}"
                                        class="btn-primary px-3! py-2!">
                                    <x-heroicon-o-check class="h-4 w-4" />
                                    {{ __('app.services.provider_requests.accept') }}
                                </button>
                            @elseif ($serviceRequest->status === \App\Enums\ServiceRequestStatus::Accepted)
                                <button type="button" wire:click="start({{ $serviceRequest->getKey() }})"
                                        wire:confirm="{{ __('app.services.provider_requests.confirm_start') }}"
                                        class="btn-primary px-3! py-2!">
                                    <x-heroicon-o-play class="h-4 w-4" />
                                    {{ __('app.services.provider_requests.start') }}
                                </button>
                            @elseif ($serviceRequest->status === \App\Enums\ServiceRequestStatus::InProgress)
                                <button type="button" wire:click="complete({{ $serviceRequest->getKey() }})"
                                        wire:confirm="{{ __('app.services.provider_requests.confirm_complete') }}"
                                        class="btn-primary px-3! py-2!">
                                    <x-heroicon-o-check-circle class="h-4 w-4" />
                                    {{ __('app.services.provider_requests.complete') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 border-t border-slate-100 pt-3 sm:grid-cols-2">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider_requests.address') }}</p>
                            @if (in_array($serviceRequest->status, [\App\Enums\ServiceRequestStatus::Accepted, \App\Enums\ServiceRequestStatus::InProgress, \App\Enums\ServiceRequestStatus::Completed], true))
                                <p class="mt-1 text-xs font-semibold text-slate-700">{{ $serviceRequest->address }}</p>
                            @else
                                <p class="mt-1 text-xs text-slate-400">{{ __('app.services.provider_requests.contact_hidden') }}</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider_requests.phone') }}</p>
                            @if (in_array($serviceRequest->status, [\App\Enums\ServiceRequestStatus::Accepted, \App\Enums\ServiceRequestStatus::InProgress, \App\Enums\ServiceRequestStatus::Completed], true))
                                <a href="tel:{{ $serviceRequest->contact_phone }}" class="mt-1 inline-block text-xs font-semibold text-brand-700" dir="ltr">{{ $serviceRequest->contact_phone }}</a>
                            @else
                                <p class="mt-1 text-xs text-slate-400">{{ __('app.services.provider_requests.contact_hidden') }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($serviceRequest->status === \App\Enums\ServiceRequestStatus::Assigned)
                        <details class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <summary class="cursor-pointer text-xs font-bold text-slate-600">{{ __('app.services.provider_requests.reject') }}</summary>
                            <div class="mt-2 flex flex-wrap items-end gap-2">
                                <div class="min-w-0 flex-1">
                                    <label for="reject-reason-{{ $serviceRequest->getKey() }}" class="mb-1 block text-[11px] font-bold text-slate-500">
                                        {{ __('app.services.provider_requests.reject_reason') }}
                                    </label>
                                    <input id="reject-reason-{{ $serviceRequest->getKey() }}" type="text" wire:model="rejectReason" maxlength="500" class="input-field">
                                </div>
                                <button type="button" wire:click="reject({{ $serviceRequest->getKey() }})"
                                        wire:confirm="{{ __('app.services.provider_requests.confirm_reject') }}"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                    <x-heroicon-o-x-circle class="h-4 w-4" />
                                    {{ __('app.services.provider_requests.reject') }}
                                </button>
                            </div>
                        </details>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    @endif
</div>
