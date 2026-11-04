@php
    $user = auth()->user();
    $typeLabel = $user->isAdmin() ? __('app.auth.administrator') : $user->account_type->label();

    if ($user->isRegularUser()) {
        $verificationLabel = __('app.account.sidebar.verification_not_required');
        $verificationColor = 'gray';
    } else {
        $verificationStatus = $user->profileVerificationStatus();
        $verificationLabel = $verificationStatus?->label() ?? __('app.account.common.pending');
        $verificationColor = $verificationStatus?->color() ?? 'warning';
    }

    $items = [
        ['route' => 'account.dashboard', 'label' => __('app.account.nav.dashboard'), 'icon' => 'heroicon-o-squares-2x2'],
        ['route' => 'account.profile', 'label' => __('app.account.nav.profile'), 'icon' => 'heroicon-o-user-circle'],
        ['route' => 'account.security', 'label' => __('app.account.nav.security'), 'icon' => 'heroicon-o-lock-closed'],
        ['route' => 'account.properties.index', 'label' => 'عقاراتي', 'icon' => 'heroicon-o-home-modern', 'match' => 'account.properties.*'],
    ];

    if ($user->isCompany()) {
        $items[] = ['route' => 'account.company', 'label' => __('app.account.nav.company'), 'icon' => 'heroicon-o-building-office-2'];
    }

    if ($user->isBroker()) {
        $items[] = ['route' => 'account.broker', 'label' => __('app.account.nav.broker'), 'icon' => 'heroicon-o-briefcase'];
    }

    $needsAttention = ($user->isBroker() || $user->isCompany()) && ! $user->isVerifiedAccount();
@endphp

{{-- Mobile navigation --}}
<nav class="flex gap-2 overflow-x-auto pb-1 lg:hidden" aria-label="{{ __('app.account.title') }}">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}"
           @class([
               'inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition',
               'bg-brand-700 text-white shadow-sm' => request()->routeIs($item['match'] ?? $item['route']),
               'border border-slate-200 bg-white text-slate-600' => ! request()->routeIs($item['match'] ?? $item['route']),
           ])
           @if (request()->routeIs($item['match'] ?? $item['route'])) aria-current="page" @endif>
            <x-dynamic-component :component="$item['icon']" class="h-4 w-4" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

{{-- Desktop sidebar --}}
<aside class="hidden space-y-4 lg:block">
    <div class="card-surface p-4">
        <div class="flex items-center gap-3">
            @if ($user->avatar_path)
                <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="" class="h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200">
            @else
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-lg font-extrabold text-brand-700 ring-1 ring-brand-100">
                    {{ mb_substr($user->name, 0, 1) }}
                </span>
            @endif

            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-slate-900">{{ $user->name }}</p>
                <p class="truncate text-[11px] text-slate-500" dir="ltr">{{ $user->email }}</p>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            <x-ui.status-badge :label="$typeLabel" :color="$user->isAdmin() ? 'info' : $user->account_type->color()" />
            <x-ui.status-badge :label="$verificationLabel" :color="$verificationColor" />
        </div>

        <nav class="mt-4 grid gap-1 border-t border-slate-100 pt-3" aria-label="{{ __('app.account.title') }}">
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-bold transition',
                       'bg-brand-50 text-brand-800' => request()->routeIs($item['match'] ?? $item['route']),
                       'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($item['match'] ?? $item['route']),
                   ])
                   @if (request()->routeIs($item['match'] ?? $item['route'])) aria-current="page" @endif>
                    <x-dynamic-component :component="$item['icon']" class="h-4.5 w-4.5" />
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="{{ route('properties.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                <x-heroicon-o-home-modern class="h-4.5 w-4.5" />
                {{ __('app.nav.properties') }}
            </a>

            @if ($user->isAdmin())
                <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                    <x-heroicon-o-cog-6-tooth class="h-4.5 w-4.5" />
                    {{ __('app.auth.admin_panel') }}
                </a>
            @endif
        </nav>

        @if ($needsAttention)
            <p class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2.5 text-[11px] font-semibold leading-5 text-amber-800">
                <x-heroicon-o-clock class="mt-0.5 h-4 w-4 shrink-0" />
                {{ $user->profileVerificationStatus()?->value === 'rejected' ? __('app.account.sidebar.rejected_note') : __('app.account.sidebar.pending_note') }}
            </p>
        @endif

        <div class="mt-3 border-t border-slate-100 pt-3">
            <p class="px-3 text-[11px] font-semibold text-slate-400">
                {{ __('app.account.sidebar.member_since', ['date' => $user->created_at?->translatedFormat('F Y') ?? '—']) }}
            </p>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold text-rose-600 transition hover:bg-rose-50">
                    <x-heroicon-o-arrow-right-start-on-rectangle class="h-4.5 w-4.5" />
                    {{ __('app.account.common.logout') }}
                </button>
            </form>
        </div>
    </div>
</aside>
