<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <nav aria-label="مسار التنقل" class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <a href="{{ route('services.index') }}" class="transition hover:text-brand-700">{{ __('app.services.title') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-slate-600">{{ __('app.services.request.title') }}</span>
            </nav>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.services.request.title') }}</h1>
            <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.services.request.subtitle') }}</p>
        </div>

        <a href="{{ route('services.index') }}" class="btn-outline shrink-0">
            <x-heroicon-o-arrow-right class="h-4 w-4" />
            {{ __('app.services.title') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0" />
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form wire:submit="submit" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-6">
            <section class="card-surface p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-base font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-wrench-screwdriver class="h-4 w-4" />
                    </span>
                    {{ __('app.services.request.service_details') }}
                </h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="service-category" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.category') }} <span class="text-rose-500">*</span></label>
                        <select id="service-category" wire:model.live="service_category_id" class="select-field @error('service_category_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_category') }}</option>
                            @foreach ($categories as $categoryOption)
                                <option value="{{ $categoryOption->getKey() }}">{{ $categoryOption->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('service_category_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="service-provider" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.provider') }}</label>
                        <select id="service-provider" wire:model.live="service_provider_id" class="select-field @error('service_provider_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_provider') }}</option>
                            @foreach ($providers as $providerOption)
                                <option value="{{ $providerOption->getKey() }}">{{ $providerOption->display_name }} — {{ $providerOption->governorate?->name_ar }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ __('app.services.request.provider_hint') }}</p>
                        @error('service_provider_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="service-governorate" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.governorate') }} <span class="text-rose-500">*</span></label>
                        <select id="service-governorate" wire:model.live="governorate_id" class="select-field @error('governorate_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_governorate') }}</option>
                            @foreach ($governorates as $governorateOption)
                                <option value="{{ $governorateOption->getKey() }}">{{ $governorateOption->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('governorate_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="service-city" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.city') }}</label>
                        <select id="service-city" wire:model="city_id" class="select-field @error('city_id') border-rose-400 @enderror">
                            <option value="">{{ __('app.services.request.choose_city') }}</option>
                            @foreach ($cities as $cityOption)
                                <option value="{{ $cityOption->getKey() }}">{{ $cityOption->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="service-title" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.title_label') }} <span class="text-rose-500">*</span></label>
                        <input id="service-title" type="text" wire:model="title" maxlength="150"
                               placeholder="{{ __('app.services.request.title_placeholder') }}"
                               class="input-field @error('title') border-rose-400 @enderror">
                        @error('title') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="service-description" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.description') }} <span class="text-rose-500">*</span></label>
                        <textarea id="service-description" wire:model="description" rows="5" maxlength="3000"
                                  placeholder="{{ __('app.services.request.description_placeholder') }}"
                                  class="input-field @error('description') border-rose-400 @enderror"></textarea>
                        @error('description') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="service-address" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.address') }} <span class="text-rose-500">*</span></label>
                        <input id="service-address" type="text" wire:model="address" maxlength="500"
                               class="input-field @error('address') border-rose-400 @enderror">
                        <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ __('app.services.request.address_hint') }}</p>
                        @error('address') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="service-phone" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.phone') }} <span class="text-rose-500">*</span></label>
                        <input id="service-phone" type="tel" wire:model="contact_phone" maxlength="20" dir="ltr"
                               class="input-field text-start @error('contact_phone') border-rose-400 @enderror">
                        @error('contact_phone') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="service-date" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.preferred_date') }}</label>
                            <input id="service-date" type="date" wire:model="preferred_date"
                                   min="{{ now()->toDateString() }}"
                                   class="input-field @error('preferred_date') border-rose-400 @enderror">
                            @error('preferred_date') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="service-time" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.services.request.preferred_time') }}</label>
                            <input id="service-time" type="time" wire:model="preferred_time"
                                   class="input-field @error('preferred_time') border-rose-400 @enderror">
                            @error('preferred_time') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="card-surface p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-base font-extrabold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-heroicon-o-photo class="h-4 w-4" />
                    </span>
                    {{ __('app.services.request.photos') }}
                </h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">{{ __('app.services.request.photos_hint') }}</p>

                <div class="mt-4">
                    <input type="file" wire:model="photos" multiple accept="image/jpeg,image/png,image/webp"
                           class="block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600 file:me-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:border-brand-300">
                    <div wire:loading wire:target="photos" class="mt-2 text-xs font-semibold text-brand-700">{{ __('app.common.saving') }}...</div>
                    @error('photos.*') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                    @error('photos') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                @if ($photos !== [])
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($photos as $index => $photo)
                            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50" wire:key="photo-{{ $index }}">
                                @if ($photo->isPreviewable())
                                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-28 w-full object-cover">
                                @else
                                    <div class="flex h-28 w-full flex-col items-center justify-center gap-1 bg-rose-50 px-2 text-center text-[11px] font-semibold text-rose-700">
                                        <x-heroicon-o-document class="h-5 w-5" />
                                        <span>{{ $photo->getClientOriginalName() }}</span>
                                    </div>
                                @endif
                                <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute end-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-600 text-white shadow-sm transition hover:bg-rose-700"
                                        aria-label="{{ __('app.services.request.remove_photo') }}">
                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <section class="card-surface p-5">
                <h2 class="text-sm font-extrabold text-slate-900">{{ __('app.services.request.summary') }}</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">{{ __('app.services.request.category') }}</dt>
                        <dd class="text-end font-bold text-slate-900">
                            {{ $categories->firstWhere('id', $service_category_id)?->name_ar ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">{{ __('app.services.request.provider') }}</dt>
                        <dd class="text-end font-bold text-slate-900">
                            {{ $providers->firstWhere('id', $service_provider_id)?->display_name ?? __('app.services.my_requests.not_assigned') }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">{{ __('app.services.request.governorate') }}</dt>
                        <dd class="text-end font-bold text-slate-900">
                            {{ $governorates->firstWhere('id', $governorate_id)?->name_ar ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">{{ __('app.services.request.photos') }}</dt>
                        <dd class="text-end font-bold text-slate-900">{{ number_format(count($photos)) }}</dd>
                    </div>
                </dl>

                <button type="submit" class="btn-primary mt-5 w-full justify-center py-3!" wire:loading.attr="disabled" wire:target="submit">
                    <x-heroicon-o-paper-airplane class="h-4 w-4" />
                    <span wire:loading.remove wire:target="submit">{{ __('app.services.request.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('app.services.request.sending') }}</span>
                </button>
            </section>

            <p class="flex items-start gap-2 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-[11px] leading-6 text-brand-900">
                <x-heroicon-o-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                <span>{{ __('app.services.request.privacy') }}</span>
            </p>
        </aside>
    </form>
</div>
