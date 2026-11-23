<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 backdrop-blur-lg supports-[backdrop-filter]:bg-white/80">
    {{-- Utility bar --}}
    <div class="hidden border-b border-white/10 bg-brand-950 text-brand-100 sm:block">
        <div class="container-aqari flex h-9 items-center justify-between text-[11px] font-medium">
            <p class="flex items-center gap-2">
                <x-heroicon-o-sparkles class="h-3.5 w-3.5 text-gold-400" />
                الإمداد — منصة مصرية ببيانات واضحة وصور حقيقية
            </p>
            <div class="flex items-center gap-6">
                <a href="tel:+201000000000" class="flex items-center gap-1.5 transition hover:text-white">
                    <x-heroicon-o-phone class="h-3.5 w-3.5" />
                    <span dir="ltr">+20 100 000 0000</span>
                </a>
                <span class="h-3 w-px bg-white/15" aria-hidden="true"></span>
                <a href="mailto:hello@emdad.test" class="flex items-center gap-1.5 transition hover:text-white">
                    <x-heroicon-o-envelope class="h-3.5 w-3.5" />
                    <span>hello@emdad.test</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Main navigation --}}
    <div class="container-aqari flex h-[72px] items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-3" aria-label="{{ __('app.brand') }}">
            <img src="{{ asset('images/logo.svg') }}" alt="" class="h-11 w-11 rounded-2xl shadow-card ring-1 ring-slate-200/60 transition group-hover:scale-[1.03]">
            <span class="leading-tight">
                <span class="block text-xl font-extrabold tracking-tight text-slate-900">{{ __('app.brand') }}</span>
                <span class="block text-[10px] font-bold tracking-[0.18em] text-brand-600">{{ __('app.tagline') }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-0.5 xl:flex" aria-label="التنقل الرئيسي">
            @php
                $navItems = [
                    ['route' => 'home', 'label' => __('app.nav.home'), 'active' => request()->routeIs('home')],
                    ['route' => 'properties.index', 'label' => __('app.nav.properties'), 'active' => request()->routeIs('properties.*') && ! request('purpose')],
                    ['route' => 'properties.index', 'params' => ['purpose' => 'sale'], 'label' => __('app.nav.for_sale'), 'active' => request('purpose') === 'sale'],
                    ['route' => 'properties.index', 'params' => ['purpose' => 'rent'], 'label' => __('app.nav.for_rent'), 'active' => request('purpose') === 'rent'],
                    ['route' => 'services.index', 'label' => __('app.services.nav'), 'active' => request()->routeIs('services.*')],
                ];
            @endphp

            @foreach ($navItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                   @class([
                       'relative rounded-lg px-3.5 py-2 text-sm font-bold transition duration-150',
                       'text-brand-700' => $item['active'],
                       'text-slate-600 hover:text-brand-700' => ! $item['active'],
                   ])>
                    {{ $item['label'] }}
                    @if ($item['active'])
                        <span class="absolute inset-x-3.5 -bottom-[3px] h-0.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach

            <a href="{{ route('home') }}#governorates" class="rounded-lg px-3.5 py-2 text-sm font-bold text-slate-600 transition hover:text-brand-700">
                {{ __('app.nav.governorates') }}
            </a>
            <a href="{{ route('home') }}#how-it-works" class="rounded-lg px-3.5 py-2 text-sm font-bold text-slate-600 transition hover:text-brand-700">
                {{ __('app.nav.how_it_works') }}
            </a>
        </nav>

        <div class="hidden items-center gap-2.5 xl:flex">
            <a href="{{ auth()->check() ? route('account.properties.create') : route('register') }}"
               class="btn-ghost px-3.5! py-2.5! text-slate-700! hover:text-brand-700!">
                <x-heroicon-o-plus-circle class="h-4.5 w-4.5" />
                أضف عقارك
            </a>

            @auth
                @php
                    $user = auth()->user();
                    $isPendingProfessional = ($user->isBroker() || $user->isCompany()) && ! $user->isVerifiedAccount();
                @endphp

                <details class="group relative" data-menu>
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pe-3 ps-1.5 text-sm font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-700 [&::-webkit-details-marker]:hidden">
                        @if ($user->avatar_path)
                            <img src="{{ Storage::disk('public')->url($user->avatar_path) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                        @else
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-heroicon-o-user class="h-4.5 w-4.5" />
                            </span>
                        @endif
                        <span class="max-w-32 truncate">{{ $user->name }}</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                    </summary>

                    <div class="absolute end-0 top-full z-50 mt-2 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="border-b border-slate-100 bg-sand-50/70 px-4 py-3">
                            <p class="truncate text-sm font-extrabold text-slate-900">{{ $user->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500" dir="ltr">{{ $user->email }}</p>
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-600 ring-1 ring-slate-200">
                                <x-heroicon-o-identification class="h-3 w-3" />
                                {{ $user->isAdmin() ? __('app.auth.administrator') : $user->account_type->label() }}
                            </span>
                        </div>

                        @if ($isPendingProfessional)
                            <p class="flex items-start gap-2 border-b border-amber-100 bg-amber-50 px-4 py-3 text-[11px] font-semibold leading-5 text-amber-800">
                                <x-heroicon-o-clock class="mt-0.5 h-4 w-4 shrink-0" />
                                {{ __('app.auth.pending_notice') }}
                            </p>
                        @endif

                        <div class="p-1.5">
                            <a href="{{ route('account.dashboard') }}"
                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                                {{ __('app.account.title') }}
                            </a>

                            <a href="{{ route('account.service-requests.index') }}"
                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                {{ __('app.services.my_requests.title') }}
                            </a>

                            <a href="{{ route('account.service-provider') }}"
                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-heroicon-o-wrench-screwdriver class="h-4 w-4" />
                                {{ $user->serviceProvider ? __('app.services.provider.title') : __('app.services.provider.register_title') }}
                            </a>

                            @if ($user->isAdmin())
                                <a href="{{ url('/admin') }}"
                                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                                    {{ __('app.auth.admin_panel') }}
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                    <x-heroicon-o-arrow-right-start-on-rectangle class="h-4 w-4" />
                                    {{ __('app.auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="btn-outline px-4! py-2.5!">
                    {{ __('app.auth.login') }}
                </a>
                <a href="{{ route('register') }}" class="btn-primary px-4! py-2.5!">
                    <x-heroicon-o-user-plus class="h-4 w-4" />
                    {{ __('app.auth.register') }}
                </a>
            @endauth
        </div>

        {{-- Mobile toggle --}}
        <details class="group xl:hidden" data-menu>
            <summary class="inline-flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-brand-300 hover:text-brand-700 [&::-webkit-details-marker]:hidden"
                     aria-label="فتح القائمة">
                <x-heroicon-o-bars-3 class="h-6 w-6 group-open:hidden" />
                <x-heroicon-o-x-mark class="hidden h-6 w-6 group-open:block" />
            </summary>

            <div id="mobile-menu" class="absolute inset-x-0 top-full max-h-[calc(100vh-72px)] overflow-y-auto border-t border-slate-200 bg-white shadow-xl">
                <nav class="container-aqari grid gap-1 py-4" aria-label="قائمة الموبايل">
                    <a href="{{ route('home') }}" class="nav-link">{{ __('app.nav.home') }}</a>
                    <a href="{{ route('properties.index') }}" class="nav-link">{{ __('app.nav.properties') }}</a>
                    <a href="{{ route('properties.index', ['purpose' => 'sale']) }}" class="nav-link">{{ __('app.nav.for_sale') }}</a>
                    <a href="{{ route('properties.index', ['purpose' => 'rent']) }}" class="nav-link">{{ __('app.nav.for_rent') }}</a>
                    <a href="{{ route('services.index') }}" class="nav-link">{{ __('app.services.nav') }}</a>
                    <a href="{{ route('home') }}#governorates" class="nav-link">{{ __('app.nav.governorates') }}</a>
                    <a href="{{ route('home') }}#how-it-works" class="nav-link">{{ __('app.nav.how_it_works') }}</a>
                    <a href="{{ auth()->check() ? route('account.properties.create') : route('register') }}" class="btn-primary mt-2">
                        <x-heroicon-o-plus-circle class="h-4 w-4" />
                        أضف عقارك
                    </a>

                    <div class="mt-2 grid gap-1 border-t border-slate-100 pt-3">
                        @auth
                            @php($user = auth()->user())
                            <p class="px-3 py-1 text-xs font-semibold text-slate-400">
                                {{ __('app.auth.account_menu') }}
                            </p>
                            <div class="flex items-center gap-2.5 rounded-xl bg-sand-50 px-3 py-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-brand-700 ring-1 ring-slate-200">
                                    <x-heroicon-o-user class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-extrabold text-slate-900">{{ $user->name }}</span>
                                    <span class="block truncate text-[11px] text-slate-500">{{ $user->isAdmin() ? __('app.auth.administrator') : $user->account_type->label() }}</span>
                                </span>
                            </div>

                            @if (($user->isBroker() || $user->isCompany()) && ! $user->isVerifiedAccount())
                                <p class="flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold leading-5 text-amber-800">
                                    <x-heroicon-o-clock class="mt-0.5 h-4 w-4 shrink-0" />
                                    {{ __('app.auth.pending_notice') }}
                                </p>
                            @endif

                            <a href="{{ route('account.dashboard') }}" class="nav-link flex items-center gap-2">
                                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                                {{ __('app.account.title') }}
                            </a>

                            <a href="{{ route('account.service-requests.index') }}" class="nav-link flex items-center gap-2">
                                <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                {{ __('app.services.my_requests.title') }}
                            </a>

                            <a href="{{ route('account.service-provider') }}" class="nav-link flex items-center gap-2">
                                <x-heroicon-o-wrench-screwdriver class="h-4 w-4" />
                                {{ $user->serviceProvider ? __('app.services.provider.title') : __('app.services.provider.register_title') }}
                            </a>

                            @if ($user->isAdmin())
                                <a href="{{ url('/admin') }}" class="nav-link flex items-center gap-2">
                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                                    {{ __('app.auth.admin_panel') }}
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                    <x-heroicon-o-arrow-right-start-on-rectangle class="h-4 w-4" />
                                    {{ __('app.auth.logout') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-outline">{{ __('app.auth.login') }}</a>
                            <a href="{{ route('register') }}" class="btn-primary">{{ __('app.auth.register') }}</a>
                        @endauth
                    </div>
                </nav>
            </div>
        </details>
    </div>
</header>
