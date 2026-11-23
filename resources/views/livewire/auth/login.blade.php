<div class="container-aqari py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl items-stretch gap-8 lg:grid-cols-[1fr_minmax(0,440px)]">
        {{-- Brand panel --}}
        <div class="relative hidden overflow-hidden rounded-3xl bg-brand-950 p-9 text-white shadow-lift lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-25"
                 style="background-image: url('{{ asset('images/hero-city.svg') }}'); background-size: cover; background-position: center;"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/85 to-brand-900/60"></div>
            <div class="hero-grid absolute inset-0 opacity-50" aria-hidden="true"></div>
            <div class="absolute -end-16 -top-16 h-56 w-56 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="" class="h-11 w-11 rounded-2xl ring-1 ring-white/15">
                    <span class="leading-tight">
                        <span class="block text-xl font-extrabold text-white">{{ __('app.brand') }}</span>
                        <span class="block text-[10px] font-bold tracking-[0.18em] text-brand-300">{{ __('app.tagline') }}</span>
                    </span>
                </a>

                <h2 class="mt-10 text-3xl font-extrabold leading-tight text-white">
                    {{ __('app.auth.login_hero_title') }}
                </h2>
                <p class="mt-3 max-w-md text-sm leading-8 text-brand-100/90">
                    {{ __('app.auth.login_hero_text') }}
                </p>

                <ul class="mt-8 space-y-3 text-sm text-brand-50">
                    @foreach ([
                        __('app.auth.login_point_1'),
                        __('app.auth.login_point_2'),
                        __('app.auth.login_point_3'),
                    ] as $point)
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-gold-300 ring-1 ring-white/15">
                                <x-heroicon-s-check class="h-3.5 w-3.5" />
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative mt-10 rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur">
                <div class="flex items-center gap-1 text-gold-300">
                    @for ($i = 0; $i < 5; $i++)
                        <x-heroicon-s-star class="h-4 w-4" />
                    @endfor
                </div>
                <p class="mt-2 text-xs leading-6 text-brand-100">
                    "تجربة بحث واضحة وسريعة، وكل التفاصيل موجودة قبل ما أزور العقار."
                </p>
                <p class="mt-2 text-[11px] font-bold text-white">أحد عملاء الإمداد</p>
            </div>
        </div>

        {{-- Form panel --}}
        <div class="card-surface w-full self-center p-6 sm:p-8">
            <span class="section-eyebrow">
                <x-heroicon-o-arrow-left-start-on-rectangle class="h-3.5 w-3.5" />
                مرحبًا بعودتك
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('app.auth.login_title') }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ __('app.auth.login_subtitle') }}</p>

            <form wire:submit="login" class="mt-6 space-y-4">
                <div>
                    <label for="login-email" class="field-label">{{ __('app.auth.email') }}</label>
                    <div class="relative">
                        <x-heroicon-o-envelope class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input id="login-email"
                               type="email"
                               wire:model="email"
                               required
                               autofocus
                               autocomplete="username"
                               dir="ltr"
                               class="input-field ps-10! text-start"
                               placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="login-password" class="field-label">{{ __('app.auth.password') }}</label>
                    <div class="relative">
                        <x-heroicon-o-lock-closed class="pointer-events-none absolute start-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input id="login-password"
                               type="password"
                               wire:model="password"
                               required
                               autocomplete="current-password"
                               dir="ltr"
                               class="input-field ps-10! text-start"
                               placeholder="••••••••">
                    </div>
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
