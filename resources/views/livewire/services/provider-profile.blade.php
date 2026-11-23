<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">
                {{ $provider ? __('app.services.provider.title') : __('app.services.provider.register_title') }}
            </h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">
                {{ $provider ? __('app.services.provider.subtitle') : __('app.services.provider.register_hint') }}
            </p>
        </div>

        @if ($provider)
            <a href="{{ route('account.service-provider.requests') }}" class="btn-primary shrink-0">
                <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                {{ __('app.services.provider.view_requests') }}
            </a>
        @endif
    </div>

    @if ($savedMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $savedMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
            {{ $errorMessage }}
        </div>
    @endif

    @if ($provider)
        @php
            $notice = match ($provider->status) {
                \App\Enums\ServiceProviderStatus::Pending => ['class' => 'border-amber-200 bg-amber-50 text-amber-800', 'icon' => 'heroicon-o-clock', 'text' => __('app.services.provider.pending_notice')],
                \App\Enums\ServiceProviderStatus::Rejected => ['class' => 'border-rose-200 bg-rose-50 text-rose-800', 'icon' => 'heroicon-o-x-circle', 'text' => __('app.services.provider.rejected_notice')],
                \App\Enums\ServiceProviderStatus::Suspended => ['class' => 'border-rose-200 bg-rose-50 text-rose-800', 'icon' => 'heroicon-o-no-symbol', 'text' => __('app.services.provider.suspended_notice')],
                \App\Enums\ServiceProviderStatus::Active => ['class' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'icon' => 'heroicon-o-check-badge', 'text' => __('app.services.provider.active_notice')],
            };
        @endphp

        <div class="flex items-start gap-2 rounded-xl border px-4 py-3 text-sm font-semibold {{ $notice['class'] }}" role="status">
            <x-dynamic-component :component="$notice['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
            <div class="min-w-0">
                <p>{{ $notice['text'] }}</p>
                @if ($provider->status === \App\Enums\ServiceProviderStatus::Rejected && $provider->rejection_reason)
                    <p class="mt-1 text-xs font-medium opacity-80">{{ $provider->rejection_reason }}</p>
                @endif
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider.stats.requests') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($requestsCount) }}</p>
            </div>
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider.stats.completed') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($completedCount) }}</p>
            </div>
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider.stats.rating') }}</p>
                <div class="mt-1 flex items-center gap-2">
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format((float) $provider->rating_avg, 1) }}</p>
                    <x-ui.stars :rating="$provider->rating_avg" />
                </div>
            </div>
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold text-slate-400">{{ __('app.services.provider.stats.reviews') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($provider->reviews_count) }}</p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-6">
            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.provider.title') }}</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="provider-name" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.display_name') }} <span class="text-rose-500">*</span></label>
                        <input id="provider-name" type="text" wire:model="display_name" maxlength="120"
                               class="input-field @error('display_name') border-rose-400 @enderror">
                        @error('display_name') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider-phone" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.phone') }} <span class="text-rose-500">*</span></label>
                        <input id="provider-phone" type="tel" wire:model="phone" maxlength="20" dir="ltr"
                               class="input-field text-start @error('phone') border-rose-400 @enderror">
                        @error('phone') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider-governorate" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.governorate') }} <span class="text-rose-500">*</span></label>
                        <select id="provider-governorate" wire:model.live="governorate_id" class="select-field @error('governorate_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_governorate') }}</option>
                            @foreach ($governorates as $governorateOption)
                                <option value="{{ $governorateOption->getKey() }}">{{ $governorateOption->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('governorate_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider-city" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.city') }}</label>
                        <select id="provider-city" wire:model="city_id" class="select-field @error('city_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_city') }}</option>
                            @foreach ($cities as $cityOption)
                                <option value="{{ $cityOption->getKey() }}">{{ $cityOption->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider-experience" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.experience_years') }} <span class="text-rose-500">*</span></label>
                        <input id="provider-experience" type="number" wire:model="experience_years" min="0" max="60"
                               class="input-field @error('experience_years') border-rose-400 @enderror">
                        @error('experience_years') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider-areas" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.service_areas') }}</label>
                        <input id="provider-areas" type="text" wire:model="service_areas_text" maxlength="500"
                               class="input-field @error('service_areas_text') border-rose-400 @enderror">
                        <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ __('app.services.provider.service_areas_hint') }}</p>
                        @error('service_areas_text') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="provider-bio" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.provider.bio') }}</label>
                        <textarea id="provider-bio" wire:model="bio" rows="4" maxlength="2000"
                                  placeholder="{{ __('app.services.provider.bio_placeholder') }}"
                                  class="input-field @error('bio') border-rose-400 @enderror"></textarea>
                        @error('bio') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.provider.categories') }} <span class="text-rose-500">*</span></h2>
                @error('category_ids') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror

                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $categoryOption)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-brand-300 has-checked:border-brand-300 has-checked:bg-brand-50">
                            <input type="checkbox" wire:model="category_ids" value="{{ $categoryOption->getKey() }}"
                                   class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-dynamic-component :component="$categoryOption->icon ?: 'heroicon-o-wrench-screwdriver'" class="h-4 w-4" />
                            </span>
                            {{ $categoryOption->name_ar }}
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">{{ __('app.services.provider.photos') }}</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">{{ __('app.services.provider.photos_hint') }}</p>

                <input type="file" wire:model="photos" multiple accept="image/jpeg,image/png,image/webp"
                       class="mt-4 block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600 file:me-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:border-brand-300">
                <div wire:loading wire:target="photos" class="mt-2 text-xs font-semibold text-brand-700">{{ __('app.common.saving') }}...</div>
                @error('photos.*') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror

                @if ($provider && $provider->images->isNotEmpty())
                    <div class="mt-5">
                        <h3 class="text-xs font-bold text-slate-600">{{ __('app.services.provider.portfolio') }}</h3>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($provider->images as $image)
                                <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50" wire:key="provider-image-{{ $image->getKey() }}">
                                    <img src="{{ $image->url() }}" alt="" class="h-24 w-full object-cover">
                                    <button type="button" wire:click="removeImage({{ $image->getKey() }})"
                                            wire:confirm="{{ __('app.services.provider.delete_image') }}؟"
                                            class="absolute end-1.5 top-1.5 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-rose-600 text-white shadow-sm transition hover:bg-rose-700"
                                            aria-label="{{ __('app.services.provider.delete_image') }}">
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <section class="card-surface p-5">
                <h2 class="text-sm font-extrabold text-slate-900">{{ __('app.services.provider.stats.reviews') }}</h2>

                @if ($latestReviews->isEmpty())
                    <p class="mt-3 rounded-xl bg-slate-50 px-3 py-4 text-center text-xs text-slate-500">{{ __('app.services.provider.no_reviews') }}</p>
                @else
                    <div class="mt-3 space-y-3">
                        @foreach ($latestReviews as $review)
                            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-xs font-extrabold text-slate-800">{{ $review->customer?->name ?? '—' }}</p>
                                    <x-ui.stars :rating="$review->rating" />
                                </div>
                                @if ($review->comment)
                                    <p class="mt-1.5 line-clamp-2 text-[11px] leading-5 text-slate-500">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <button type="submit" class="btn-primary w-full justify-center py-3!" wire:loading.attr="disabled" wire:target="save">
                <x-heroicon-o-check class="h-4 w-4" />
                <span wire:loading.remove wire:target="save">{{ __('app.services.provider.save') }}</span>
                <span wire:loading wire:target="save">{{ __('app.common.saving') }}...</span>
            </button>

            <p class="flex items-start gap-2 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-[11px] leading-6 text-brand-900">
                <x-heroicon-o-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                <span>{{ __('app.services.provider.register_hint') }}</span>
            </p>
        </aside>
    </form>
</div>
