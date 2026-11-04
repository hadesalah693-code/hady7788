@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth', block: 'start' })"
        : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="التنقل بين الصفحات"
             class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold text-slate-500">
                عرض
                <span class="font-extrabold text-slate-800">{{ number_format($paginator->firstItem()) }}</span>
                –
                <span class="font-extrabold text-slate-800">{{ number_format($paginator->lastItem()) }}</span>
                من
                <span class="font-extrabold text-slate-800">{{ number_format($paginator->total()) }}</span>
                {{ __('app.listing.unit') }}
            </p>

            <div class="flex items-center gap-1.5">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                          class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300">
                        <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </span>
                @else
                    <button type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            aria-label="{{ __('pagination.previous') }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50">
                        <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </button>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true"
                              class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-xs font-bold text-slate-400">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                          class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-brand-700 px-2 text-xs font-extrabold text-white shadow-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            wire:loading.attr="disabled"
                                            aria-label="الانتقال إلى الصفحة {{ $page }}"
                                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-xs font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50">
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <button type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            aria-label="{{ __('pagination.next') }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50">
                        <x-heroicon-o-chevron-left class="h-4 w-4" />
                    </button>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                          class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300">
                        <x-heroicon-o-chevron-left class="h-4 w-4" />
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
