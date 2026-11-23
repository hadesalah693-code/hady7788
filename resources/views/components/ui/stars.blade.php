@props(['rating' => 0, 'count' => null])

@php($filled = (int) round((float) $rating))

<div {{ $attributes->merge(['class' => 'flex items-center gap-1']) }} aria-label="{{ __('app.services.review.stars', ['count' => $filled]) }}">
    @for ($star = 1; $star <= 5; $star++)
        <x-dynamic-component
            :component="$star <= $filled ? 'heroicon-s-star' : 'heroicon-o-star'"
            @class(['h-4 w-4', 'text-amber-400' => $star <= $filled, 'text-slate-300' => $star > $filled])
        />
    @endfor

    @if ($count !== null)
        <span class="text-xs font-semibold text-slate-500">({{ number_format($count) }})</span>
    @endif
</div>
