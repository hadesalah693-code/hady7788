@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('app.brand').' | '.__('app.tagline') }}</title>
    <meta name="description" content="{{ $metaDescription ?? __('app.description') }}">
    <meta name="theme-color" content="#0f766e">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('app.brand') }}">
    <meta property="og:title" content="{{ $title ?? __('app.brand').' | '.__('app.tagline') }}">
    <meta property="og:description" content="{{ $metaDescription ?? __('app.description') }}">
    <meta property="og:locale" content="ar_EG">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/cairo-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen bg-sand-50">
    <a href="#main-content"
       class="sr-only z-50 rounded-lg bg-brand-700 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:start-3">
        تخطي إلى المحتوى الرئيسي
    </a>

    <x-site.header />

    <x-site.flash />

    <main id="main-content">
        {{ $slot }}
    </main>

    <x-site.footer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
