<footer id="contact" class="mt-20 bg-slate-950 text-slate-300">
    <div class="container-aqari grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="h-10 w-10 rounded-xl">
                <span class="text-xl font-extrabold text-white">{{ __('app.brand') }}</span>
            </a>
            <p class="mt-4 text-sm leading-7 text-slate-400">{{ __('app.footer.about') }}</p>
            <div class="mt-5 flex items-center gap-2 text-xs font-semibold text-brand-300">
                <x-heroicon-o-shield-check class="h-4 w-4" />
                <span>بيانات محمية وإعلانات قابلة للمراجعة</span>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-bold text-white">{{ __('app.footer.quick_links') }}</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a href="{{ route('home') }}" class="footer-link">{{ __('app.nav.home') }}</a></li>
                <li><a href="{{ route('properties.index') }}" class="footer-link">{{ __('app.nav.properties') }}</a></li>
                <li><a href="{{ route('properties.index', ['purpose' => 'sale']) }}" class="footer-link">{{ __('app.nav.for_sale') }}</a></li>
                <li><a href="{{ route('properties.index', ['purpose' => 'rent']) }}" class="footer-link">{{ __('app.nav.for_rent') }}</a></li>
                <li><a href="{{ route('home') }}#how-it-works" class="footer-link">{{ __('app.nav.how_it_works') }}</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-bold text-white">{{ __('app.footer.property_types') }}</h2>
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
            <h2 class="text-sm font-bold text-white">{{ __('app.footer.contact') }}</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center gap-2.5">
                    <x-heroicon-o-map-pin class="h-4 w-4 text-brand-400" />
                    <span>{{ __('app.footer.address') }}</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-heroicon-o-phone class="h-4 w-4 text-brand-400" />
                    <a href="tel:+201000000000" class="footer-link" dir="ltr">+20 100 000 0000</a>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-heroicon-o-envelope class="h-4 w-4 text-brand-400" />
                    <a href="mailto:hello@emdad.test" class="footer-link">{{ __('app.footer.email') }}</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-aqari flex flex-col items-center justify-between gap-2 py-5 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ now()->year }} {{ __('app.brand') }}. {{ __('app.footer.rights') }}.</p>
            <p class="flex items-center gap-1.5">
                <x-heroicon-o-heart class="h-3.5 w-3.5 text-amber-400" />
                {{ __('app.footer.made_in') }}
            </p>
        </div>
    </div>
</footer>
