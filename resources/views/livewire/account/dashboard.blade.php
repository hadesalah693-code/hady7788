@php
    $typeLabel = $user->isAdmin() ? __('app.auth.administrator') : $user->account_type->label();
    $verificationStatus = $user->profileVerificationStatus();

    if ($user->isRegularUser() || $user->isAdmin()) {
        $verificationLabel = __('app.account.sidebar.verification_not_required');
        $verificationColor = 'gray';
    } else {
        $verificationLabel = $verificationStatus?->label() ?? __('app.account.common.pending');
        $verificationColor = $verificationStatus?->color() ?? 'warning';
    }

    $needsAttention = ($user->isBroker() || $user->isCompany()) && ! $user->isVerifiedAccount();
    $professionalRoute = $user->isCompany() ? 'account.company' : 'account.broker';
@endphp

<div class="space-y-6">
    <section class="card-surface overflow-hidden">
        <div class="bg-gradient-to-l from-brand-800 via-brand-900 to-brand-950 p-6 text-white sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand-200">{{ __('app.account.eyebrow') }}</p>
                    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ __('app.account.welcome', ['name' => $user->name]) }}</h1>
                    <p class="mt-2 max-w-xl text-sm leading-7 text-brand-50/80">{{ __('app.account.subtitle') }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="badge bg-white/15 text-white ring-1 ring-white/25">{{ $typeLabel }}</span>
                    <span class="badge bg-white/15 text-white ring-1 ring-white/25">{{ $verificationLabel }}</span>
                </div>
            </div>
        </div>
    </section>

    @if ($needsAttention)
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-start gap-2 text-sm font-semibold leading-6 text-amber-900">
                <x-heroicon-o-clock class="mt-0.5 h-5 w-5 shrink-0" />
                {{ $verificationStatus?->value === 'rejected' ? __('app.account.sidebar.rejected_note') : __('app.account.sidebar.pending_note') }}
            </p>
            <a href="{{ route($professionalRoute) }}" class="btn-accent shrink-0 px-4! py-2.5!">
                <x-heroicon-o-pencil-square class="h-4 w-4" />
                {{ $user->isCompany() ? __('app.account.company.title') : __('app.account.broker.title') }}
            </a>
        </div>
    @endif

    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div class="card-surface p-4 sm:p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                <x-heroicon-o-home-modern class="h-5 w-5" />
            </span>
            <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ $propertiesCount }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ __('app.account.dashboard.stats.properties') }}</p>
        </div>

        <div class="card-surface p-4 sm:p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                <x-heroicon-o-check-badge class="h-5 w-5" />
            </span>
            <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ $publishedCount }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ __('app.account.dashboard.stats.published') }}</p>
        </div>

        <div class="card-surface p-4 sm:p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <x-heroicon-o-identification class="h-5 w-5" />
            </span>
            <p class="mt-3 truncate text-lg font-extrabold text-slate-900">{{ $typeLabel }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ __('app.account.dashboard.stats.account_type') }}</p>
        </div>

        <div class="card-surface p-4 sm:p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                <x-heroicon-o-shield-check class="h-5 w-5" />
            </span>
            <p class="mt-3 truncate text-lg font-extrabold text-slate-900">{{ $verificationLabel }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ __('app.account.dashboard.stats.verification') }}</p>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="card-surface p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.account.dashboard.quick_actions') }}</h2>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('account.profile') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-user-circle class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-slate-900">{{ __('app.account.dashboard.actions.profile') }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.dashboard.actions.profile_text') }}</p>
                </a>

                <a href="{{ route('account.security') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-lock-closed class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-slate-900">{{ __('app.account.dashboard.actions.security') }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.dashboard.actions.security_text') }}</p>
                </a>

                @if ($user->isCompany())
                    <a href="{{ route('account.company') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-heroicon-o-building-office-2 class="h-5 w-5" />
                        </span>
                        <p class="mt-3 text-sm font-extrabold text-slate-900">{{ __('app.account.dashboard.actions.company') }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.dashboard.actions.company_text') }}</p>
                    </a>
                @endif

                @if ($user->isBroker())
                    <a href="{{ route('account.broker') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-heroicon-o-briefcase class="h-5 w-5" />
                        </span>
                        <p class="mt-3 text-sm font-extrabold text-slate-900">{{ __('app.account.dashboard.actions.broker') }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.dashboard.actions.broker_text') }}</p>
                    </a>
                @endif

                <a href="{{ route('account.properties.index') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-home-modern class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-slate-900">عقاراتي</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">راجع عقاراتك وعدّلها وأدر حالتها.</p>
                </a>

                <a href="{{ route('account.properties.create') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-plus class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-slate-900">إضافة عقار</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">أضف عقارًا جديدًا واحفظه كمسودة أو أرسله للمراجعة.</p>
                </a>

                <a href="{{ route('properties.index') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-slate-900">{{ __('app.account.dashboard.actions.browse') }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.dashboard.actions.browse_text') }}</p>
                </a>
            </div>
        </section>

        <section class="card-surface p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.account.dashboard.info') }}</h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.name') }}</dt>
                    <dd class="text-end font-bold text-slate-900">{{ $user->name }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.email') }}</dt>
                    <dd class="min-w-0 truncate text-end font-bold text-slate-900" dir="ltr">{{ $user->email }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.phone') }}</dt>
                    <dd class="font-bold text-slate-900" dir="ltr">{{ $user->phone ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.whatsapp') }}</dt>
                    <dd class="font-bold text-slate-900" dir="ltr">{{ $user->whatsapp ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.location') }}</dt>
                    <dd class="text-end font-bold text-slate-900">
                        {{ collect([$user->city?->name_ar, $user->governorate?->name_ar])->filter()->implode('، ') ?: '—' }}
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-slate-500">{{ __('app.account.dashboard.fields.status') }}</dt>
                    <dd><x-ui.status-badge :label="$user->status->label()" :color="$user->status->color()" /></dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="card-surface p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-extrabold text-slate-900">{{ __('app.account.dashboard.properties_title') }}</h2>
            <span class="text-xs font-semibold text-slate-400">{{ $propertiesCount }}</span>
        </div>

        @if ($latestProperties->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-8 text-center">
                <x-heroicon-o-home-modern class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 text-sm font-semibold text-slate-600">{{ __('app.account.dashboard.properties_empty') }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ __('app.account.dashboard.properties_note') }}</p>
            </div>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($latestProperties as $property)
                    <li class="flex items-center gap-3 py-3">
                        <img src="{{ $property->image_url }}" alt="" class="h-14 w-16 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $property->title }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $property->location_label }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-ui.status-badge :label="$property->status->label()" :color="$property->status->color()" />
                            <a href="{{ route('properties.show', $property) }}" class="text-xs font-bold text-brand-700 hover:text-brand-900">
                                {{ __('app.account.dashboard.properties_view') }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>

            <p class="mt-3 text-[11px] leading-5 text-slate-400">{{ __('app.account.dashboard.properties_note') }}</p>
        @endif
    </section>
</div>
