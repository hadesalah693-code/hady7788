<?php

namespace App\Livewire\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\City;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Services\ServiceImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('طلب خدمة جديدة')]
class ServiceRequestForm extends Component
{
    use WithFileUploads;

    public ?int $service_category_id = null;

    public ?int $service_provider_id = null;

    public ?int $governorate_id = null;

    public ?int $city_id = null;

    public string $title = '';

    public string $description = '';

    public string $address = '';

    public string $contact_phone = '';

    public string $preferred_date = '';

    public string $preferred_time = '';

    /** @var array<int, mixed> */
    public array $photos = [];

    public function mount(): void
    {
        Gate::authorize('create', ServiceRequest::class);

        $user = auth()->user();

        $this->contact_phone = (string) ($user->phone ?? '');
        $this->governorate_id = $user->governorate_id;
        $this->city_id = $user->city_id;

        if (request()->filled('category')) {
            $category = ServiceCategory::query()
                ->active()
                ->where('slug', request('category'))
                ->first();

            $this->service_category_id = $category?->getKey();
        }

        if (request()->filled('provider')) {
            $provider = ServiceProvider::query()
                ->available()
                ->with('categories')
                ->find(request('provider'));

            if ($provider !== null) {
                $this->service_provider_id = $provider->getKey();
                $this->service_category_id ??= $provider->categories->first()?->getKey();
                $this->governorate_id ??= $provider->governorate_id;
                $this->city_id ??= $provider->city_id;
            }
        }
    }

    public function updatedServiceCategoryId(): void
    {
        if ($this->service_provider_id === null) {
            return;
        }

        $provider = ServiceProvider::query()->find($this->service_provider_id);

        $servesCategory = $provider !== null
            && $provider->categories()->whereKey($this->service_category_id)->exists();

        if (! $servesCategory) {
            $this->service_provider_id = null;
        }
    }

    public function updatedGovernorateId(): void
    {
        $this->city_id = null;
    }

    public function updatedServiceProviderId(): void
    {
        if ($this->service_provider_id === null) {
            return;
        }

        $provider = ServiceProvider::query()->available()->with('categories')->find($this->service_provider_id);

        if ($provider === null) {
            return;
        }

        $this->governorate_id = $provider->governorate_id;
        $this->city_id = $provider->city_id;
        $this->service_category_id ??= $provider->categories->first()?->getKey();
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function submit(): mixed
    {
        Gate::authorize('create', ServiceRequest::class);

        $this->validate([
            'service_category_id' => ['required', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'service_provider_id' => ['nullable', Rule::exists('service_providers', 'id')->where('status', 'active')->where('is_available', true)],
            'governorate_id' => ['required', Rule::exists('governorates', 'id')->where('is_active', true)],
            'city_id' => ['nullable', Rule::exists('cities', 'id')->where('governorate_id', $this->governorate_id)],
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'contact_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'photos' => ['array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'contact_phone.regex' => 'أدخل رقم هاتف صحيحًا (أرقام ورمز + فقط).',
            'description.min' => 'اكتب وصفًا واضحًا للمشكلة (20 حرفًا على الأقل).',
        ]);

        $user = auth()->user();

        $maxOpen = (int) config('service_module.requests.max_open_per_customer', 10);

        if ($user->serviceRequests()->open()->count() >= $maxOpen) {
            $this->addError('title', 'لديك عدد كبير من الطلبات المفتوحة. تابع طلباتك الحالية أولًا.');

            return null;
        }

        $provider = $this->service_provider_id !== null
            ? ServiceProvider::query()->available()->findOrFail($this->service_provider_id)
            : null;

        $serviceRequest = DB::transaction(function () use ($user, $provider): ServiceRequest {
            $serviceRequest = ServiceRequest::create([
                'reference' => $this->generateReference(),
                'user_id' => $user->getKey(),
                'service_category_id' => $this->service_category_id,
                'service_provider_id' => $provider?->getKey(),
                'governorate_id' => $this->governorate_id,
                'city_id' => $this->city_id,
                'title' => $this->title,
                'description' => $this->description,
                'address' => $this->address,
                'contact_phone' => $this->contact_phone,
                'preferred_date' => $this->preferred_date ?: null,
                'preferred_time' => $this->preferred_time ?: null,
                'status' => $provider !== null ? ServiceRequestStatus::Assigned : ServiceRequestStatus::New,
                'assigned_at' => $provider !== null ? now() : null,
            ]);

            foreach ($this->photos as $photo) {
                app(ServiceImageService::class)->storeForRequest($serviceRequest, $photo);
            }

            return $serviceRequest;
        });

        session()->flash('status', 'تم إرسال طلب الخدمة بنجاح. رقم المتابعة: '.$serviceRequest->reference);

        return redirect()->route('account.service-requests.show', $serviceRequest);
    }

    public function render(): View
    {
        $categories = ServiceCategory::query()->active()->ordered()->get();

        $providers = ServiceProvider::query()
            ->available()
            ->with(['categories', 'governorate'])
            ->when($this->service_category_id !== null, fn ($query) => $query->forCategory((int) $this->service_category_id))
            ->when($this->governorate_id !== null, fn ($query) => $query->where('governorate_id', $this->governorate_id))
            ->orderByDesc('rating_avg')
            ->orderBy('display_name')
            ->get();

        $governorates = Governorate::query()
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();

        $cities = $this->governorate_id !== null
            ? City::query()
                ->where('governorate_id', $this->governorate_id)
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get()
            : collect();

        return view('livewire.services.service-request-form', [
            'categories' => $categories,
            'providers' => $providers,
            'governorates' => $governorates,
            'cities' => $cities,
        ]);
    }

    private function generateReference(): string
    {
        do {
            $reference = 'SR-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (ServiceRequest::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }
}
