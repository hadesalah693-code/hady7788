<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('app.account.profile.title') }}</h1>
        <p class="mt-1.5 text-sm leading-7 text-slate-500">{{ __('app.account.profile.subtitle') }}</p>
    </div>

    @if ($savedMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $savedMessage }}
        </div>
    @endif

    <form wire:submit="save" class="card-surface p-5 sm:p-6">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-center">
            <div class="shrink-0">
                @if ($avatar && $avatar->isPreviewable())
                    <img src="{{ $avatar->temporaryUrl() }}" alt="" class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200">
                @elseif ($currentAvatar)
                    <img src="{{ asset('storage/'.$currentAvatar) }}" alt="" class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200">
                @else
                    <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-50 text-2xl font-extrabold text-brand-700 ring-1 ring-brand-100">
                        {{ mb_substr($name ?: auth()->user()->name, 0, 1) }}
                    </span>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-slate-900">{{ __('app.account.profile.photo') }}</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('app.account.common.upload_hint') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <label for="profile-avatar" class="btn-outline cursor-pointer px-4! py-2.5!">
                        <x-heroicon-o-camera class="h-4 w-4" />
                        {{ __('app.account.common.change_photo') }}
                        <input id="profile-avatar"
                               type="file"
                               wire:model="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               class="sr-only">
                    </label>

                    @if ($currentAvatar && ! $avatar)
                        <button type="button"
                                wire:click="removeAvatar"
                                wire:confirm="هل تريد إزالة الصورة الشخصية؟"
                                class="btn-ghost px-4! py-2.5! text-rose-600 hover:bg-rose-50">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            {{ __('app.account.common.remove_photo') }}
                        </button>
                    @endif

                    <span wire:loading wire:target="avatar" class="text-xs font-semibold text-slate-500">
                        {{ __('app.account.common.saving') }}
                    </span>
                </div>

                @error('avatar')
                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="profile-name" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.profile.name') }}</label>
                <input id="profile-name" type="text" wire:model="name" required autocomplete="name" class="input-field">
                @error('name')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-email" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.profile.email') }}</label>
                <input id="profile-email" type="email" wire:model="email" required autocomplete="email" dir="ltr" class="input-field text-start">
                <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.account.profile.email_hint') }}</p>
                @error('email')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-phone" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.profile.phone') }}</label>
                <input id="profile-phone" type="tel" wire:model="phone" autocomplete="tel" dir="ltr" class="input-field text-start" placeholder="01000000000">
                @error('phone')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-whatsapp" class="mb-1.5 block text-xs font-bold text-slate-600">
                    {{ __('app.account.profile.whatsapp') }}
                    <span class="font-normal text-slate-400">({{ __('app.account.common.optional') }})</span>
                </label>
                <input id="profile-whatsapp" type="tel" wire:model="whatsapp" dir="ltr" class="input-field text-start" placeholder="01000000000">
                <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.account.profile.whatsapp_hint') }}</p>
                @error('whatsapp')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-governorate" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.profile.governorate') }}</label>
                <select id="profile-governorate" wire:model.live="governorate_id" class="select-field">
                    <option value="">{{ __('app.account.common.choose') }}</option>
                    @foreach ($governorates as $governorate)
                        <option value="{{ $governorate->id }}">{{ $governorate->name_ar }}</option>
                    @endforeach
                </select>
                @error('governorate_id')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile-city" class="mb-1.5 block text-xs font-bold text-slate-600">{{ __('app.account.profile.city') }}</label>
                <select id="profile-city" wire:model="city_id" class="select-field" @disabled($cities->isEmpty())>
                    <option value="">{{ __('app.account.common.choose') }}</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name_ar }}</option>
                    @endforeach
                </select>
                @if ($cities->isEmpty())
                    <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.account.profile.city_hint') }}</p>
                @endif
                @error('city_id')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="profile-bio" class="mb-1.5 block text-xs font-bold text-slate-600">
                    {{ __('app.account.profile.bio') }}
                    <span class="font-normal text-slate-400">({{ __('app.account.common.optional') }})</span>
                </label>
                <textarea id="profile-bio" wire:model="bio" rows="4" maxlength="1000" class="input-field resize-y"></textarea>
                <p class="mt-1.5 text-[11px] leading-5 text-slate-400">{{ __('app.account.profile.bio_hint') }}</p>
                @error('bio')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('account.dashboard') }}" class="btn-ghost">{{ __('app.account.common.back') }}</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save,avatar">
                <x-heroicon-o-check class="h-4 w-4" wire:loading.remove wire:target="save" />
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span wire:loading.remove wire:target="save">{{ __('app.account.profile.save') }}</span>
                <span wire:loading wire:target="save">{{ __('app.account.common.saving') }}</span>
            </button>
        </div>
    </form>
</div>
