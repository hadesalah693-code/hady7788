<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.account.security.title') }}</h1>
        <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.account.security.subtitle') }}</p>
    </div>

    @if ($savedMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $savedMessage }}
        </div>
    @endif

    <form wire:submit="updatePassword" class="card-surface p-5 sm:p-6">
        <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-4">
            <x-heroicon-o-shield-check class="mt-0.5 h-5 w-5 shrink-0 text-brand-700" />
            <div>
                <p class="text-sm font-bold text-slate-800">{{ __('app.account.security.hint') }}</p>
                <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ __('app.account.security.session_note') }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 sm:max-w-md">
            <div>
                <label for="security-current" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.security.current') }}</label>
                <input id="security-current"
                       type="password"
                       wire:model="current_password"
                       required
                       autocomplete="current-password"
                       dir="ltr"
                       class="input-field text-start"
                       placeholder="••••••••">
                @error('current_password')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="security-new" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.security.new') }}</label>
                <input id="security-new"
                       type="password"
                       wire:model="password"
                       required
                       autocomplete="new-password"
                       dir="ltr"
                       class="input-field text-start"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="security-confirm" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.security.confirm') }}</label>
                <input id="security-confirm"
                       type="password"
                       wire:model="password_confirmation"
                       required
                       autocomplete="new-password"
                       dir="ltr"
                       class="input-field text-start"
                       placeholder="••••••••">
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('account.dashboard') }}" class="btn-ghost">{{ __('app.account.common.back') }}</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updatePassword">
                <x-heroicon-o-key class="h-4 w-4" wire:loading.remove wire:target="updatePassword" />
                <svg wire:loading wire:target="updatePassword" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span wire:loading.remove wire:target="updatePassword">{{ __('app.account.security.submit') }}</span>
                <span wire:loading wire:target="updatePassword">{{ __('app.account.common.saving') }}</span>
            </button>
        </div>
    </form>
</div>
