<div class="container-aqari py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl items-center gap-10 lg:grid-cols-[1fr_minmax(0,420px)]">
        <div class="hidden lg:block">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="h-11 w-11 rounded-xl shadow-sm">
                <span class="leading-tight">
                    <span class="block text-xl font-extrabold text-slate-900">{{ __('app.brand') }}</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Al Emdad</span>
                </span>
            </a>

            <h2 class="mt-8 text-3xl font-extrabold leading-tight text-slate-900">
                {{ __('app.auth.login_hero_title') }}
            </h2>
            <p class="mt-3 max-w-md text-sm leading-8 text-slate-500">
                {{ __('app.auth.login_hero_text') }}
            </p>

            <ul class="mt-7 space-y-3 text-sm text-slate-600">
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.login_point_1') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.login_point_2') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.login_point_3') }}
                </li>
            </ul>
        </div>

        <div class="card-surface w-full p-6 sm:p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('app.auth.login_title') }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ __('app.auth.login_subtitle') }}</p>

            <form wire:submit="login" class="mt-6 space-y-4">
                <div>
                    <label for="login-email" class="mb-1.5 block text-xs font-bold text-slate-600">
                        {{ __('app.auth.email') }}
                    </label>
                    <input id="login-email"
                           type="email"
                           wire:model="email"
                           required
                           autofocus
                           autocomplete="username"
                           dir="ltr"
                           class="input-field text-start"
                           placeholder="name@example.com">
                    @error('email')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="login-password" class="mb-1.5 block text-xs font-bold text-slate-600">
                        {{ __('app.auth.password') }}
                    </label>
                    <input id="login-password"
                           type="password"
                           wire:model="password"
                           required
                           autocomplete="current-password"
                           dir="ltr"
                           class="input-field text-start"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600">
                    <input type="checkbox"
                           wire:model="remember"
                           class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                    {{ __('app.auth.remember') }}
                </label>

                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="login">
                    <x-heroicon-o-arrow-left-start-on-rectangle class="h-4 w-4" wire:loading.remove wire:target="login" />
                    <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span wire:loading.remove wire:target="login">{{ __('app.auth.login_button') }}</span>
                    <span wire:loading wire:target="login">{{ __('app.auth.logging_in') }}</span>
                </button>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
                {{ __('app.auth.no_account') }}
                <a href="{{ route('register') }}" class="font-bold text-brand-700 transition hover:text-brand-900">
                    {{ __('app.auth.create_account') }}
                </a>
            </p>
        </div>
    </div>
</div>
