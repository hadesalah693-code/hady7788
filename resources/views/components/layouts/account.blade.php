@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('app.account.title').' | '.__('app.brand') }}</title>
    <meta name="description" content="{{ __('app.account.subtitle') }}">
    <meta name="theme-color" content="#0f766e">
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/cairo-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen bg-sand-50">
    <a href="#account-content"
       class="sr-only z-50 rounded-lg bg-brand-700 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:start-3">
        تخطي إلى المحتوى الرئيسي
    </a>

    <x-site.header />

    <x-site.flash />

    <main id="account-content" class="container-aqari py-6 sm:py-10">
        <div class="grid gap-6 lg:grid-cols-[286px_minmax(0,1fr)] lg:items-start">
            <x-account.sidebar />

            <div class="min-w-0 space-y-6">
                {{ $slot }}
            </div>
        </div>
    </main>

    <x-site.footer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
