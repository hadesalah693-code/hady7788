@php
    $verificationStatus = $profile?->verification_status;
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.account.broker.title') }}</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.account.broker.subtitle') }}</p>
        </div>

        @if ($verificationStatus)
            <x-ui.status-badge :label="$verificationStatus->label()" :color="$verificationStatus->color()" />
        @endif
    </div>

    @if ($savedMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $savedMessage }}
        </div>
    @endif

    @if ($reverificationTriggered)
        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold leading-6 text-amber-900" role="alert">
            <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0" />
            {{ __('app.account.broker.reverify_triggered') }}
        </div>
    @endif

    <section class="card-surface p-5 sm:p-6">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                <x-heroicon-o-building-storefront class="h-5 w-5" />
            </span>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-500">{{ __('app.account.broker.company') }}</p>
                <p class="mt-1 truncate text-sm font-extrabold text-slate-900">
                    {{ $companyName ?: __('app.account.broker.no_company') }}
                </p>
            </div>
        </div>
    </section>

    <form wire:submit="save" class="card-surface p-5 sm:p-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="broker-title" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.broker.professional_title') }}</label>
                <input id="broker-title" type="text" wire:model="professional_title" class="input-field" placeholder="{{ __('app.account.broker.professional_title_hint') }}">
                @error('professional_title')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="broker-license" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.broker.license_number') }}</label>
                <input id="broker-license" type="text" wire:model="license_number" dir="ltr" class="input-field text-start" placeholder="BR-123456">
                @error('license_number')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="broker-years" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.broker.years') }}</label>
                <input id="broker-years" type="number" min="0" max="70" wire:model="years_of_experience" class="input-field">
                @error('years_of_experience')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="broker-bio" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.broker.bio') }}</label>
                <textarea id="broker-bio" wire:model="bio" rows="4" maxlength="2000" class="input-field resize-y"></textarea>
                @error('bio')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <fieldset class="sm:col-span-2">
                <legend class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.broker.service_areas') }}</legend>
                <p class="mb-3 text-[11px] leading-5 text-slate-400">{{ __('app.account.broker.service_areas_hint') }}</p>

                <div class="grid max-h-72 gap-2 overflow-y-auto rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($governorates as $governorate)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-brand-50/60">
                            <input type="checkbox"
                                   wire:model="service_areas"
                                   value="{{ $governorate->name_ar }}"
                                   class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                            <span class="truncate">{{ $governorate->name_ar }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400">{{ __('app.account.broker.no_areas') }}</p>
                    @endforelse
                </div>

                @error('service_areas')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
                @error('service_areas.*')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <p class="mt-5 flex items-start gap-2 rounded-xl bg-slate-50 px-4 py-3 text-[11px] leading-5 text-slate-500">
            <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0" />
            {{ __('app.account.broker.reverify_note') }}
        </p>

        <div class="mt-5 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('account.dashboard') }}" class="btn-ghost">{{ __('app.account.common.back') }}</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                <x-heroicon-o-check class="h-4 w-4" wire:loading.remove wire:target="save" />
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span wire:loading.remove wire:target="save">{{ __('app.account.broker.save') }}</span>
                <span wire:loading wire:target="save">{{ __('app.account.common.saving') }}</span>
            </button>
        </div>
    </form>
</div>
