<div class="container-aqari py-10 sm:py-16">
    <div class="mx-auto grid max-w-6xl items-start gap-10 lg:grid-cols-[1fr_minmax(0,520px)]">
        <div class="hidden lg:block">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="h-11 w-11 rounded-xl shadow-sm">
                <span class="leading-tight">
                    <span class="block text-xl font-extrabold text-slate-900">{{ __('app.brand') }}</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Al Emdad</span>
                </span>
            </a>

            <h2 class="mt-8 text-3xl font-extrabold leading-tight text-slate-900">
                {{ __('app.auth.register_hero_title') }}
            </h2>
            <p class="mt-3 max-w-md text-sm leading-8 text-slate-500">
                {{ __('app.auth.register_hero_text') }}
            </p>

            <ul class="mt-7 space-y-3 text-sm text-slate-600">
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.register_point_1') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.register_point_2') }}
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-brand-600" />
                    {{ __('app.auth.register_point_3') }}
                </li>
            </ul>
        </div>

        <div class="card-surface w-full p-6 sm:p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('app.auth.register_title') }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ __('app.auth.register_subtitle') }}</p>

            <form wire:submit="register" class="mt-6 space-y-5">
                <fieldset>
                    <legend class="mb-2 block text-xs font-bold text-slate-600">{{ __('app.auth.account_type') }}</legend>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($accountTypes as $type)
                            <label @class([
                                'relative flex cursor-pointer flex-col gap-2 rounded-xl border p-3 transition',
                                'border-brand-600 bg-brand-50/60 ring-2 ring-brand-100' => $account_type === $type->value,
                                'border-slate-200 bg-white hover:border-brand-300' => $account_type !== $type->value,
                            ])>
                                <input type="radio"
                                       wire:model.live="account_type"
                                       value="{{ $type->value }}"
                                       class="sr-only">

                                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                                    <x-dynamic-component :component="$type->iconComponent()" class="h-5 w-5 text-brand-700" />
                                    {{ $type->label() }}
                                </span>
                                <span class="text-[11px] leading-5 text-slate-500">{{ $type->description() }}</span>

                                @if ($type->requiresVerification())
                                    <span class="mt-auto inline-flex w-fit items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                        <x-heroicon-o-shield-check class="h-3 w-3" />
                                        يتطلب توثيقًا
                                    </span>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <p class="mt-2 text-[11px] leading-5 text-slate-400">{{ __('app.auth.account_type_hint') }}</p>

                    @error('account_type')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div>
                    <label for="register-name" class="mb-1.5 block text-xs font-bold text-slate-600">
                        {{ __('app.auth.name') }}
                    </label>
                    <input id="register-name"
                           type="text"
                           wire:model="name"
                           required
                           autofocus
                           autocomplete="name"
                           class="input-field"
                           placeholder="محمد أحمد">
                    @error('name')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="register-email" class="mb-1.5 block text-xs font-bold text-slate-600">
                            {{ __('app.auth.email') }}
                        </label>
                        <input id="register-email"
                               type="email"
                               wire:model="email"
                               required
                               autocomplete="email"
                               dir="ltr"
                               class="input-field text-start"
                               placeholder="name@example.com">
                        @error('email')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="register-phone" class="mb-1.5 block text-xs font-bold text-slate-600">
                            {{ __('app.auth.phone') }}
                        </label>
                        <input id="register-phone"
                               type="tel"
                               wire:model="phone"
                               autocomplete="tel"
                               dir="ltr"
                               class="input-field text-start"
                               placeholder="+20 100 000 0000">
                        @error('phone')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($account_type === 'company')
                    <div>
                        <label for="register-company-name" class="mb-1.5 block text-xs font-bold text-slate-600">
                            {{ __('app.auth.company_name') }}
                        </label>
                        <input id="register-company-name"
                               type="text"
                               wire:model="company_name"
                               class="input-field"
                               placeholder="شركة الإمداد للعقارات">
                        <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.auth.company_name_hint') }}</p>
                        @error('company_name')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="register-password" class="mb-1.5 block text-xs font-bold text-slate-600">
                            {{ __('app.auth.password') }}
                        </label>
                        <input id="register-password"
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
                        <label for="register-password-confirmation" class="mb-1.5 block text-xs font-bold text-slate-600">
                            {{ __('app.auth.password_confirmation') }}
                        </label>
                        <input id="register-password-confirmation"
                               type="password"
                               wire:model="password_confirmation"
                               required
                               autocomplete="new-password"
                               dir="ltr"
                               class="input-field text-start"
                               placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <label class="flex cursor-pointer items-start gap-2 text-sm font-semibold text-slate-600">
                        <input type="checkbox"
                               wire:model="terms"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                        <span class="leading-6">{{ __('app.auth.terms') }}</span>
                    </label>
                    @error('terms')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="register">
                    <x-heroicon-o-user-plus class="h-4 w-4" wire:loading.remove wire:target="register" />
                    <svg wire:loading wire:target="register" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span wire:loading.remove wire:target="register">{{ __('app.auth.register_button') }}</span>
                    <span wire:loading wire:target="register">{{ __('app.auth.registering') }}</span>
                </button>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
                {{ __('app.auth.have_account') }}
                <a href="{{ route('login') }}" class="font-bold text-brand-700 transition hover:text-brand-900">
                    {{ __('app.auth.sign_in') }}
                </a>
            </p>
        </div>
    </div>
</div>
