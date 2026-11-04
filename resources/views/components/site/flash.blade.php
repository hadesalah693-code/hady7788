@if (session('status'))
    <div data-flash class="border-b border-emerald-100 bg-emerald-50" role="status">
        <div class="container-aqari flex items-center justify-between gap-3 py-3">
            <p class="flex items-center gap-2 text-sm font-semibold text-emerald-800">
                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                {{ session('status') }}
            </p>
            <button type="button"
                    data-dismiss
                    class="rounded-lg p-1 text-emerald-700 transition hover:bg-emerald-100"
                    aria-label="إغلاق">
                <x-heroicon-o-x-mark class="h-4 w-4" />
            </button>
        </div>
    </div>
@endif
