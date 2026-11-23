<?php

namespace App\Livewire\Services;

use App\Enums\ServiceProviderStatus;
use App\Models\City;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Services\ServiceImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.account')]
#[Title('حساب مقدم الخدمة')]
class ProviderProfile extends Component
{
    use WithFileUploads;

    public ?ServiceProvider $provider = null;

    public string $display_name = '';

    public string $phone = '';

    public string $bio = '';

    public int $experience_years = 0;

    public ?int $governorate_id = null;

    public ?int $city_id = null;

    public string $service_areas_text = '';

    /** @var array<int, int> */
    public array $category_ids = [];

    /** @var array<int, mixed> */
    public array $photos = [];

    public ?string $savedMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->provider = auth()->user()->serviceProvider?->load(['categories', 'images', 'governorate', 'city']);

        if ($this->provider === null) {
            $this->phone = (string) (auth()->user()->phone ?? '');
            $this->governorate_id = auth()->user()->governorate_id;
            $this->city_id = auth()->user()->city_id;

            return;
        }

        $this->display_name = $this->provider->display_name;
        $this->phone = $this->provider->phone;
        $this->bio = (string) $this->provider->bio;
        $this->experience_years = (int) $this->provider->experience_years;
        $this->governorate_id = $this->provider->governorate_id;
        $this->city_id = $this->provider->city_id;
        $this->service_areas_text = implode('، ', (array) ($this->provider->service_areas ?? []));
        $this->category_ids = $this->provider->categories->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function updatedGovernorateId(): void
    {
        $this->city_id = null;
    }

    public function save(): void
    {
        $this->savedMessage = null;
        $this->errorMessage = null;
        $this->resetErrorBag();

        $creating = $this->provider === null;

        if ($creating) {
            Gate::authorize('create', ServiceProvider::class);
        } else {
            Gate::authorize('update', $this->provider);
        }

        $this->validate([
            'display_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'governorate_id' => ['required', Rule::exists('governorates', 'id')->where('is_active', true)],
            'city_id' => ['nullable', Rule::exists('cities', 'id')->where('governorate_id', $this->governorate_id)],
            'service_areas_text' => ['nullable', 'string', 'max:500'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'photos' => ['array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'category_ids.required' => 'اختر تخصصًا واحدًا على الأقل.',
            'phone.regex' => 'أدخل رقم هاتف صحيحًا (أرقام ورمز + فقط).',
        ]);

        $areas = collect(preg_split('/[,،\n]+/u', $this->service_areas_text) ?: [])
            ->map(fn (string $area): string => trim($area))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $provider = DB::transaction(function () use ($creating, $areas): ServiceProvider {
            $attributes = [
                'display_name' => $this->display_name,
                'phone' => $this->phone,
                'bio' => $this->bio ?: null,
                'experience_years' => $this->experience_years,
                'governorate_id' => $this->governorate_id,
                'city_id' => $this->city_id,
                'service_areas' => $areas,
            ];

            if ($creating) {
                $attributes['user_id'] = auth()->id();
                $attributes['status'] = ServiceProviderStatus::Pending;
                $attributes['is_available'] = true;

                $provider = ServiceProvider::create($attributes);
            } else {
                $provider = $this->provider;

                if ($provider->status === ServiceProviderStatus::Rejected) {
                    $attributes['status'] = ServiceProviderStatus::Pending;
                    $attributes['rejection_reason'] = null;
                }

                $provider->forceFill($attributes)->save();
            }

            $provider->categories()->sync($this->category_ids);

            return $provider;
        });

        if ($this->photos !== []) {
            try {
                foreach ($this->photos as $photo) {
                    app(ServiceImageService::class)->storeForProvider($provider, $photo);
                }
            } catch (\Throwable $exception) {
                $this->errorMessage = 'تم حفظ البيانات، لكن تعذر رفع بعض الصور. '.$exception->getMessage();
            }
        }

        $this->provider = $provider->refresh()->load(['categories', 'images', 'governorate', 'city']);
        $this->photos = [];
        $this->savedMessage = $creating
            ? 'تم إنشاء حساب مقدم الخدمة. سيظهر ملفك للعملاء بعد اعتماد الإدارة.'
            : 'تم تحديث بيانات مقدم الخدمة.';
    }

    public function removeImage(int $imageId): void
    {
        if ($this->provider === null) {
            return;
        }

        Gate::authorize('update', $this->provider);

        $image = $this->provider->images()->findOrFail($imageId);

        app(ServiceImageService::class)->deleteProviderImage($image);

        $this->provider = $this->provider->refresh()->load(['categories', 'images', 'governorate', 'city']);
        $this->savedMessage = 'تم حذف الصورة.';
    }

    public function render(): View
    {
        $governorates = Governorate::query()->where('is_active', true)->orderBy('name_ar')->get();

        $cities = $this->governorate_id !== null
            ? City::query()
                ->where('governorate_id', $this->governorate_id)
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get()
            : collect();

        return view('livewire.services.provider-profile', [
            'governorates' => $governorates,
            'cities' => $cities,
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'latestReviews' => $this->provider?->reviews()->with('customer')->latest()->take(5)->get() ?? collect(),
            'requestsCount' => $this->provider?->requests()->count() ?? 0,
            'completedCount' => $this->provider?->requests()->where('status', 'completed')->count() ?? 0,
        ]);
    }
}
