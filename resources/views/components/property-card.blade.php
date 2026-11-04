@props(['property'])

<article {{ $attributes }} class="group card-surface flex flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-soft">
    <a href="{{ route('properties.show', $property) }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100">
        <img src="{{ $property->image_url }}"
             alt="{{ $property->title }}"
             loading="lazy"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">

        <div class="absolute inset-x-3 top-3 flex flex-wrap items-center justify-between gap-2">
            <span class="badge {{ $property->purpose->badgeClasses() }}">
                {{ $property->purpose_label }}
            </span>

            <span class="flex items-center gap-1.5">
                @if ($property->is_featured)
                    <span class="badge bg-amber-400 text-amber-950">
                        <x-heroicon-s-star class="h-3 w-3" />
                        {{ __('app.property.featured') }}
                    </span>
                @endif

                @if ($property->is_new)
                    <span class="badge bg-white/95 text-brand-800">
                        {{ __('app.property.new') }}
                    </span>
                @endif
            </span>
        </div>

        <span class="absolute inset-x-3 bottom-3 inline-flex w-fit items-center gap-1.5 rounded-lg bg-slate-950/65 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
            <x-heroicon-o-map-pin class="h-3.5 w-3.5" />
            {{ $property->location_label ?: $property->governorate?->name_ar }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-[11px] font-bold text-brand-700">{{ $property->type?->name_ar }}</p>

        <h3 class="mt-1.5 text-base font-bold leading-7 text-slate-900">
            <a href="{{ route('properties.show', $property) }}" class="transition hover:text-brand-700">
                {{ $property->title }}
            </a>
        </h3>

        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
            @if ($property->bedrooms)
                <span class="inline-flex items-center gap-1.5">
                    <x-icons.bed class="h-4 w-4 text-brand-600" />
                    {{ $property->bedrooms }} {{ __('app.property.bedrooms') }}
                </span>
            @endif

            @if ($property->bathrooms)
                <span class="inline-flex items-center gap-1.5">
                    <x-icons.bath class="h-4 w-4 text-brand-600" />
                    {{ $property->bathrooms }} {{ __('app.property.bathrooms') }}
                </span>
            @endif

            <span class="inline-flex items-center gap-1.5">
                <x-icons.area class="h-4 w-4 text-brand-600" />
                {{ $property->area_formatted }}
            </span>
        </div>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
            <p class="text-lg font-extrabold text-brand-700">
                {{ $property->price_formatted }}
                <span class="text-[11px] font-semibold text-slate-500">
                    {{ __('app.property.currency') }}{{ $property->price_suffix }}
                </span>
            </p>

            <a href="{{ route('properties.show', $property) }}"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 transition hover:text-brand-700">
                {{ __('app.actions.details') }}
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
        </div>
    </div>
</article>
