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
        <nav role="navigation" aria-label="التنقل بين الصفحات" class="flex items-center justify-between gap-3">
            @if ($paginator->onFirstPage())
                <span class="btn-outline cursor-not-allowed opacity-50">{{ __('pagination.previous') }}</span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" class="btn-outline">
                    {{ __('pagination.previous') }}
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" class="btn-outline">
                    {{ __('pagination.next') }}
                </button>
            @else
                <span class="btn-outline cursor-not-allowed opacity-50">{{ __('pagination.next') }}</span>
            @endif
        </nav>
    @endif
</div>
