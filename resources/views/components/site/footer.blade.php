<footer id="contact" class="relative mt-24 overflow-hidden bg-brand-950 text-slate-300">
    <div class="hero-grid absolute inset-0 opacity-50" aria-hidden="true"></div>
    <div class="absolute -end-24 -top-24 h-72 w-72 rounded-full bg-brand-500/15 blur-3xl" aria-hidden="true"></div>

    {{-- Newsletter / trust strip --}}
    <div class="relative border-b border-white/10">
        <div class="container-aqari flex flex-col items-start justify-between gap-5 py-8 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-gold-300 ring-1 ring-white/15">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-extrabold text-white">بيانات محمية وإعلانات قابلة للمراجعة</p>
                    <p class="mt-0.5 text-xs text-slate-400">نراجع كل إعلان قبل نشره لضمان جودة التجربة</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold text-slate-300">
                    <x-heroicon-o-lock-closed class="h-3.5 w-3.5 text-brand-300" />
                    دفع آمن
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold text-slate-300">
                    <x-heroicon-o-check-badge class="h-3.5 w-3.5 text-brand-300" />
                    موثوقون
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold text-slate-300">
                    <x-heroicon-o-clock class="h-3.5 w-3.5 text-brand-300" />
                    دعم 24/7
                </span>
            </div>
        </div>
    </div>

    <div class="relative container-aqari grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="h-11 w-11 rounded-2xl ring-1 ring-white/10">
                <span class="leading-tight">
                    <span class="block text-xl font-extrabold text-white">{{ __('app.brand') }}</span>
                    <span class="block text-[10px] font-bold tracking-[0.18em] text-brand-300">{{ __('app.tagline') }}</span>
                </span>
            </a>
            <p class="mt-5 max-w-sm text-sm leading-7 text-slate-400">{{ __('app.footer.about') }}</p>

            <div class="mt-6 flex items-center gap-2">
                <a href="#" aria-label="Facebook"
                   class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-brand-400/40 hover:bg-brand-500/10 hover:text-white">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.91h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg>
                </a>
                <a href="#" aria-label="X"
                   class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-brand-400/40 hover:bg-brand-500/10 hover:text-white">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.22-6.82-5.96 6.82H1.66l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23Zm-1.16 17.52h1.83L7.01 4.13H5.05l12.03 15.64Z"/></svg>
                </a>
                <a href="#" aria-label="Instagram"
                   class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-brand-400/40 hover:bg-brand-500/10 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="#" aria-label="LinkedIn"
                   class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-brand-400/40 hover:bg-brand-500/10 hover:text-white">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M4.98 3.5A2.5 2.5 0 1 0 5 8.5a2.5 2.5 0 0 0-.02-5ZM3 9.75h4v11.25H3V9.75Zm6.5 0h3.83v1.54h.05c.53-.95 1.83-1.96 3.77-1.96 4.03 0 4.78 2.5 4.78 5.76V21h-4v-5.02c0-1.2-.02-2.75-1.75-2.75-1.75 0-2.02 1.3-2.02 2.66V21h-4V9.75Z"/></svg>
                </a>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-extrabold text-white">{{ __('app.footer.quick_links') }}</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a href="{{ route('home') }}" class="footer-link">{{ __('app.nav.home') }}</a></li>
                <li><a href="{{ route('properties.index') }}" class="footer-link">{{ __('app.nav.properties') }}</a></li>
                <li><a href="{{ route('properties.index', ['purpose' => 'sale']) }}" class="footer-link">{{ __('app.nav.for_sale') }}</a></li>
                <li><a href="{{ route('properties.index', ['purpose' => 'rent']) }}" class="footer-link">{{ __('app.nav.for_rent') }}</a></li>
                <li><a href="{{ route('home') }}#how-it-works" class="footer-link">{{ __('app.nav.how_it_works') }}</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-extrabold text-white">{{ __('app.footer.property_types') }}</h2>
            <ul class="mt-4 space-y-2.5">
                @foreach (\App\Models\PropertyType::query()->where('is_active', true)->orderBy('sort_order')->take(6)->get() as $footerType)
                    <li>
                        <a href="{{ route('properties.index', ['type' => $footerType->slug]) }}" class="footer-link">
                            {{ $footerType->name_ar }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-extrabold text-white">{{ __('app.footer.contact') }}</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/[0.05] text-brand-300">
                        <x-heroicon-o-map-pin class="h-4 w-4" />
                    </span>
                    <span>{{ __('app.footer.address') }}</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/[0.05] text-brand-300">
                        <x-heroicon-o-phone class="h-4 w-4" />
                    </span>
                    <a href="tel:+201000000000" class="footer-link" dir="ltr">+20 100 000 0000</a>
                </li>
                <li class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/[0.05] text-brand-300">
                        <x-heroicon-o-envelope class="h-4 w-4" />
                    </span>
                    <a href="mailto:hello@emdad.test" class="footer-link">{{ __('app.footer.email') }}</a>
                </li>
            </ul>

            <a href="{{ route('services.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.05] px-3.5 py-2.5 text-xs font-bold text-white transition hover:border-brand-400/40 hover:bg-brand-500/10">
                <x-heroicon-o-wrench-screwdriver class="h-4 w-4 text-gold-300" />
                تصفح خدمات المنزل
            </a>
        </div>
    </div>

    <div class="relative border-t border-white/10">
        <div class="container-aqari flex flex-col items-center justify-between gap-3 py-5 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ now()->year }} {{ __('app.brand') }}. {{ __('app.footer.rights') }}.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="transition hover:text-slate-300">سياسة الخصوصية</a>
                <span class="h-3 w-px bg-white/10" aria-hidden="true"></span>
                <a href="#" class="transition hover:text-slate-300">الشروط والأحكام</a>
                <span class="h-3 w-px bg-white/10" aria-hidden="true"></span>
                <p class="flex items-center gap-1.5">
                    <x-heroicon-o-heart class="h-3.5 w-3.5 text-gold-400" />
                    {{ __('app.footer.made_in') }}
                </p>
            </div>
        </div>
    </div>
</footer>
