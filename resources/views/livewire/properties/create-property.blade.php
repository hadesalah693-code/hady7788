<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">إضافة عقار جديد</h1>
        <p class="mt-1.5 text-sm leading-7 text-slate-500">
            أدخل بيانات عقارك. يمكنك حفظه كمسودة أو إرساله للمراجعة.
        </p>
    </div>

    @if ($savedMessage)
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
            {{ $savedMessage }}
        </div>
    @endif

    @if ($savedMessage && $savedPropertyId)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3">
            <p class="text-sm font-semibold leading-6 text-brand-900">
                أضف صور العقار ومرافقه الآن ليصبح جاهزًا للمراجعة.
            </p>
            <a href="{{ route('account.properties.edit', $savedPropertyId) }}" class="btn-primary shrink-0 px-4! py-2.5!">
                <x-heroicon-o-photo class="h-4 w-4" />
                إدارة الصور والمرافق
            </a>
        </div>
    @endif

    <form class="card-surface p-5 sm:p-6 space-y-6">
        <section>
            <h2 class="text-lg font-extrabold text-slate-900">المعلومات الأساسية</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">عنوان العقار</label>
                    <input type="text" wire:model="title" class="input-field" maxlength="200">
                    @error('title') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">نوع العقار</label>
                    <select wire:model="property_type_id" class="select-field">
                        <option value="">اختر...</option>
                        @foreach ($propertyTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('property_type_id') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">الغرض</label>
                    <select wire:model="purpose" class="select-field">
                        <option value="sale">للبيع</option>
                        <option value="rent">للإيجار</option>
                    </select>
                    @error('purpose') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">السعر</label>
                    <input type="number" step="0.01" min="0" wire:model="price" class="input-field" dir="ltr">
                    @error('price') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">العملة</label>
                    <select wire:model="currency" class="select-field">
                        @foreach ($currencies as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('currency') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">المساحة (م²)</label>
                    <input type="number" step="0.01" min="0" wire:model="area" class="input-field" dir="ltr">
                    @error('area') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">الوصف</label>
                    <p class="mb-2 text-xs leading-6 text-slate-500">هذا الوصف سيظهر للزوار. اذكر مميزات العقار والمنطقة العامة فقط، ولا تكتب اسم الشارع أو رقم المبنى أو الشقة.</p>
                    <textarea wire:model="description" rows="5" class="input-field resize-y"></textarea>
                    @error('description') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-slate-900">الموقع</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">المحافظة</label>
                    <select wire:model.live="governorate_id" class="select-field">
                        <option value="">اختر...</option>
                        @foreach ($governorates as $governorate)
                            <option value="{{ $governorate->id }}">{{ $governorate->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('governorate_id') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">المدينة</label>
                    <select wire:model.live="city_id" class="select-field" @disabled($cities->isEmpty())>
                        <option value="">اختر...</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">المنطقة</label>
                    <select wire:model="district_id" class="select-field" @disabled($districts->isEmpty())>
                        <option value="">اختر...</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('district_id') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">العنوان (اختياري، لن يُعرض للعامة بالتفصيل)</label>
                    <input type="text" wire:model="address" class="input-field" maxlength="255">
                    @error('address') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-slate-900">تفاصيل العقار</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">عدد الغرف</label>
                    <input type="number" min="0" wire:model="bedrooms" class="input-field" dir="ltr">
                    @error('bedrooms') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">عدد الحمامات</label>
                    <input type="number" min="0" wire:model="bathrooms" class="input-field" dir="ltr">
                    @error('bathrooms') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">الطابق</label>
                    <input type="number" min="0" wire:model="floor" class="input-field" dir="ltr">
                    @error('floor') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">عدد طوابق المبنى</label>
                    <input type="number" min="0" wire:model="total_floors" class="input-field" dir="ltr">
                    @error('total_floors') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">أماكن انتظار السيارات</label>
                    <input type="number" min="0" wire:model="parking_spaces" class="input-field" dir="ltr">
                    @error('parking_spaces') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">سنة البناء</label>
                    <input type="number" min="1900" wire:model="year_built" class="input-field" dir="ltr">
                    @error('year_built') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">حالة التشطيب</label>
                    <select wire:model="finishing" class="select-field">
                        <option value="">اختر...</option>
                        @foreach ($finishings as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('finishing') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">تاريخ الإتاحة</label>
                    <input type="date" wire:model="available_from" class="input-field" dir="ltr">
                    @error('available_from') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-slate-900">بيانات التواصل (اختياري)</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">اسم جهة التواصل</label>
                    <input type="text" wire:model="contact_name" class="input-field" maxlength="120">
                    @error('contact_name') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">هاتف</label>
                    <input type="tel" wire:model="contact_phone" class="input-field" dir="ltr">
                    @error('contact_phone') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">واتساب</label>
                    <input type="tel" wire:model="contact_whatsapp" class="input-field" dir="ltr">
                    @error('contact_whatsapp') <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('account.dashboard') }}" class="btn-ghost">رجوع</a>
            <button type="button" wire:click="saveDraft" class="btn-outline" wire:loading.attr="disabled" wire:target="saveDraft,submitForReview">
                <span wire:loading.remove wire:target="saveDraft">حفظ كمسودة</span>
                <span wire:loading wire:target="saveDraft">جارٍ الحفظ...</span>
            </button>
            <button type="button" wire:click="submitForReview" class="btn-primary" wire:loading.attr="disabled" wire:target="saveDraft,submitForReview">
                <span wire:loading.remove wire:target="submitForReview">إرسال للمراجعة</span>
                <span wire:loading wire:target="submitForReview">جارٍ الإرسال...</span>
            </button>
        </div>
    </form>
</div>
