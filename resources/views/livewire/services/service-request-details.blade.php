<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <nav aria-label="مسار التنقل" class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <a href="{{ route('account.service-requests.index') }}" class="transition hover:text-brand-700">{{ __('app.services.my_requests.title') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-slate-600">{{ $serviceRequest->reference }}</span>
            </nav>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ $serviceRequest->title }}</h1>
                <x-ui.status-badge :label="$serviceRequest->status->label()" :color="$serviceRequest->status->color()" />
            </div>

            <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-semibold text-slate-500">
                <span dir="ltr">{{ $serviceRequest->reference }}</span>
                <span>{{ $serviceRequest->created_at?->translatedFormat('d M Y — H:i') }}</span>
                <span>{{ $serviceRequest->category?->name_ar }}</span>
            </p>
        </div>

        <a href="{{ route('account.service-requests.index') }}" class="btn-outline shrink-0">
            <x-heroicon-o-arrow-right class="h-4 w-4" />
            {{ __('app.services.details.back') }}
        </a>
    </div>

    @if ($actionMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $actionMessage }}
        </div>
    @endif

    @if ($actionError)
        <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
            {{ $actionError }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-6">
            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.details.description') }}</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-8 text-slate-600">{{ $serviceRequest->description }}</p>

                <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400">{{ __('app.services.details.address') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $serviceRequest->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400">{{ __('app.services.details.phone') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-700" dir="ltr">{{ $serviceRequest->contact_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400">{{ __('app.services.details.location') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $serviceRequest->governorate?->name_ar ?? '—' }}
                            @if ($serviceRequest->city)
                                — {{ $serviceRequest->city->name_ar }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400">{{ __('app.services.details.preferred_date') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-700">
                            @if ($serviceRequest->preferred_date)
                                {{ $serviceRequest->preferred_date->translatedFormat('d M Y') }}
                                @if ($serviceRequest->preferred_time)
                                    — {{ $serviceRequest->preferred_time }}
                                @endif
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.details.images') }}</h2>

                @if ($serviceRequest->images->isEmpty())
                    <p class="mt-3 rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">{{ __('app.services.details.no_images') }}</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($serviceRequest->images as $image)
                            <a href="{{ route('service-request-images.show', $image) }}" target="_blank" rel="noopener"
                               class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img src="{{ route('service-request-images.show', $image) }}" alt="" loading="lazy"
                                     class="h-32 w-full object-cover transition group-hover:scale-105">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.details.timeline') }}</h2>

                <ol class="mt-5 space-y-4">
                    @foreach ($timeline as $step)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                @if ($step['done'])
                                    <x-heroicon-s-check class="h-4 w-4" />
                                @else
                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold {{ $step['done'] ? 'text-slate-900' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                                @if ($step['at'])
                                    <p class="mt-0.5 text-[11px] font-semibold text-slate-400">{{ $step['at']->translatedFormat('d M Y — H:i') }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach

                    @if ($serviceRequest->status === \App\Enums\ServiceRequestStatus::Cancelled)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                <x-heroicon-s-x-mark class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-rose-700">{{ __('app.services.details.cancelled') }}</p>
                                @if ($serviceRequest->cancelled_at)
                                    <p class="mt-0.5 text-[11px] font-semibold text-slate-400">{{ $serviceRequest->cancelled_at->translatedFormat('d M Y — H:i') }}</p>
                                @endif
                            </div>
                        </li>
                    @endif
                </ol>
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.details.review_title') }}</h2>

                @if ($serviceRequest->review)
                    <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <x-ui.stars :rating="$serviceRequest->review->rating" />
                            <span class="text-[11px] font-semibold text-slate-500">{{ $serviceRequest->review->created_at?->translatedFormat('d M Y') }}</span>
                        </div>
                        @if ($serviceRequest->review->comment)
                            <p class="mt-2 text-sm leading-7 text-slate-700">{{ $serviceRequest->review->comment }}</p>
                        @endif

                        @if ($serviceRequest->review->is_reported)
                            <p class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-[11px] font-bold text-rose-700 ring-1 ring-rose-200">
                                <x-heroicon-o-flag class="h-3.5 w-3.5" />
                                {{ __('app.services.details.reported') }}
                            </p>
                        @endif
                    </div>

                    @if (! $serviceRequest->review->is_reported)
                        <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <summary class="cursor-pointer text-sm font-bold text-slate-700">{{ __('app.services.details.report') }}</summary>

                            <div class="mt-3 space-y-3">
                                <label for="report-reason" class="block text-xs font-bold text-slate-600">{{ __('app.services.details.report_reason') }}</label>
                                <textarea id="report-reason" wire:model="reportReason" rows="3" maxlength="1000"
                                          placeholder="{{ __('app.services.details.report_placeholder') }}"
                                          class="input-field @error('reportReason') border-rose-400 @enderror"></textarea>
                                @error('reportReason') <p class="text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror

                                <button type="button" wire:click="reportReview" class="btn-outline px-4! py-2!">
                                    <x-heroicon-o-flag class="h-4 w-4" />
                                    {{ __('app.services.details.submit_report') }}
                                </button>
                            </div>
                        </details>
                    @endif
                @elseif ($serviceRequest->status === \App\Enums\ServiceRequestStatus::Completed)
                    <p class="mt-1 text-xs leading-6 text-slate-500">{{ __('app.services.details.review_hint') }}</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <span class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.details.rating') }}</span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @for ($star = 1; $star <= 5; $star++)
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model="rating" value="{{ $star }}" class="peer sr-only">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-300 transition peer-checked:border-amber-300 peer-checked:bg-amber-50 peer-checked:text-amber-500">
                                            <x-heroicon-s-star class="h-5 w-5" />
                                        </span>
                                    </label>
                                @endfor
                                <span class="text-sm font-bold text-slate-600">{{ $rating }} / 5</span>
                            </div>
                            @error('rating') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="review-comment" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.details.comment') }}</label>
                            <textarea id="review-comment" wire:model="comment" rows="3" maxlength="1000"
                                      placeholder="{{ __('app.services.details.comment_placeholder') }}"
                                      class="input-field @error('comment') border-rose-400 @enderror"></textarea>
                            @error('comment') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="button" wire:click="submitReview" class="btn-primary">
                            <x-heroicon-o-star class="h-4 w-4" />
                            {{ __('app.services.details.submit_review') }}
                        </button>
                    </div>
                @else
                    <p class="mt-3 rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">{{ __('app.services.details.not_completed') }}</p>
                @endif
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <section class="card-surface p-5">
                <h2 class="text-sm font-extrabold text-slate-900">{{ __('app.services.details.provider') }}</h2>

                @if ($serviceRequest->provider)
                    <div class="mt-4 flex items-start gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                            <x-heroicon-o-user class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-extrabold text-slate-900">{{ $serviceRequest->provider->display_name }}</p>
                            <x-ui.stars class="mt-1" :rating="$serviceRequest->provider->rating_avg" :count="$serviceRequest->provider->reviews_count" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach ($serviceRequest->provider->categories as $categoryOption)
                            <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ $categoryOption->name_ar }}</span>
                        @endforeach
                    </div>

                    @if ($serviceRequest->providerContactVisible())
                        <a href="tel:{{ $serviceRequest->provider->phone }}" class="btn-outline mt-4 w-full justify-center" dir="ltr">
                            <x-heroicon-o-phone class="h-4 w-4" />
                            {{ $serviceRequest->provider->phone }}
                        </a>
                    @else
                        <p class="mt-4 rounded-xl bg-slate-50 px-3 py-2.5 text-[11px] leading-5 text-slate-500">
                            {{ __('app.services.details.provider_hint') }}
                        </p>
                    @endif
                @else
                    <p class="mt-3 rounded-xl bg-slate-50 px-3 py-4 text-center text-sm text-slate-500">{{ __('app.services.my_requests.not_assigned') }}</p>
                @endif
            </section>

            @if ($serviceRequest->quoted_price !== null)
                <section class="card-surface p-5">
                    <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.details.quoted_price') }}</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-900">
                        {{ number_format((float) $serviceRequest->quoted_price, 2) }}
                        <span class="text-sm font-bold text-slate-500">{{ __('app.property.currency') }}</span>
                    </p>
                </section>
            @endif

            @can('cancel', $serviceRequest)
                <button type="button" wire:click="cancelRequest"
                        wire:confirm="{{ __('app.services.my_requests.cancel_confirm') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                    <x-heroicon-o-x-circle class="h-4 w-4" />
                    {{ __('app.services.details.cancel') }}
                </button>
            @endcan
        </aside>
    </div>
</div>
