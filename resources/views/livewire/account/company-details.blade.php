@php
    $verificationStatus = $profile?->verification_status;
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.account.company.title') }}</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.account.company.subtitle') }}</p>
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
            {{ __('app.account.company.reverify_triggered') }}
        </div>
    @endif

    <form wire:submit="save" class="card-surface p-5 sm:p-6">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-center">
            <div class="shrink-0">
                @if ($logo && $logo->isPreviewable())
                    <img src="{{ $logo->temporaryUrl() }}" alt="" class="h-20 w-20 rounded-2xl object-contain ring-1 ring-slate-200">
                @elseif ($currentLogo)
                    <img src="{{ asset('storage/'.$currentLogo) }}" alt="" class="h-20 w-20 rounded-2xl object-contain ring-1 ring-slate-200">
                @else
                    <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                        <x-heroicon-o-building-office-2 class="h-8 w-8" />
                    </span>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-slate-900">{{ __('app.account.company.logo') }}</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.common.upload_hint') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <label for="company-logo" class="btn-outline cursor-pointer px-4! py-2.5!">
                        <x-heroicon-o-photo class="h-4 w-4" />
                        {{ __('app.account.common.change_photo') }}
                        <input id="company-logo"
                               type="file"
                               wire:model="logo"
                               accept="image/jpeg,image/png,image/webp"
                               class="sr-only">
                    </label>

                    @if ($currentLogo && ! $logo)
                        <button type="button"
                                wire:click="removeLogo"
                                wire:confirm="هل تريد إزالة شعار الشركة؟"
                                class="btn-ghost px-4! py-2.5! text-rose-600 hover:bg-rose-50">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            {{ __('app.account.common.remove_photo') }}
                        </button>
                    @endif

                    <span wire:loading wire:target="logo" class="text-xs font-semibold text-slate-500">
                        {{ __('app.account.common.saving') }}
                    </span>
                </div>

                @error('logo')
                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="company-name" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.name') }}</label>
                <input id="company-name" type="text" wire:model="company_name" required class="input-field">
                @error('company_name')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company-register" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.commercial_register') }}</label>
                <input id="company-register" type="text" wire:model="commercial_register" dir="ltr" class="input-field text-start" placeholder="CR-123456">
                @error('commercial_register')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company-tax" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.tax_id') }}</label>
                <input id="company-tax" type="text" wire:model="tax_id" dir="ltr" class="input-field text-start" placeholder="123-456-789">
                @error('tax_id')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company-website" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.website') }}</label>
                <input id="company-website" type="url" wire:model="website" dir="ltr" class="input-field text-start" placeholder="https://example.com">
                <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.account.company.website_hint') }}</p>
                @error('website')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company-address" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.address') }}</label>
                <input id="company-address" type="text" wire:model="address" class="input-field">
                @error('address')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="company-description" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.company.description') }}</label>
                <textarea id="company-description" wire:model="description" rows="4" maxlength="2000" class="input-field resize-y"></textarea>
                @error('description')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <p class="mt-5 flex items-start gap-2 rounded-xl bg-slate-50 px-4 py-3 text-[11px] leading-5 text-slate-500">
            <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0" />
            {{ __('app.account.company.reverify_note') }}
        </p>

        <div class="mt-5 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('account.dashboard') }}" class="btn-ghost">{{ __('app.account.common.back') }}</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save,logo">
                <x-heroicon-o-check class="h-4 w-4" wire:loading.remove wire:target="save" />
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span wire:loading.remove wire:target="save">{{ __('app.account.company.save') }}</span>
                <span wire:loading wire:target="save">{{ __('app.account.common.saving') }}</span>
            </button>
        </div>
    </form>

    <section class="card-surface p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.account.company.team_title') }}</h2>
            <span class="badge bg-slate-100 text-slate-600">{{ __('app.account.company.brokers_count', ['count' => $brokersCount]) }}</span>
        </div>
        <p class="mt-2 text-xs leading-6 text-slate-500">{{ __('app.account.company.team_note') }}</p>
    </section>
</div>
