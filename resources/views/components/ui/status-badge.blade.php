@props(['label', 'color' => 'gray'])

@php
    $classes = match ($color) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'info' => 'bg-brand-50 text-brand-700 ring-brand-200',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ring-1 '.$classes]) }}>{{ $label }}</span>
