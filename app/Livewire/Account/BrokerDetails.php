<?php

namespace App\Livewire\Account;

use App\Enums\VerificationStatus;
use App\Models\BrokerProfile;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('بيانات الوسيط')]
class BrokerDetails extends Component
{
    public string $professional_title = '';

    public string $license_number = '';

    public ?int $years_of_experience = null;

    public string $bio = '';

    /** @var array<int, string> */
    public array $service_areas = [];

    public ?string $savedMessage = null;

    public bool $reverificationTriggered = false;

    public function mount(): void
    {
        $user = $this->currentUser();

        abort_unless($user->isBroker(), 403);

        $profile = $user->brokerProfile;

        if ($profile) {
            Gate::authorize('update', $profile);

            $this->fill([
                'professional_title' => $profile->professional_title ?? '',
                'license_number' => $profile->license_number ?? '',
                'years_of_experience' => $profile->years_of_experience,
                'bio' => $profile->bio ?? '',
                'service_areas' => array_values($profile->service_areas ?? []),
            ]);
        }
    }

    public function save(): void
    {
        $user = $this->currentUser();

        abort_unless($user->isBroker(), 403);

        $profile = $user->brokerProfile()->firstOrCreate([], [
            'professional_title' => $this->professional_title !== '' ? $this->professional_title : null,
        ]);

        Gate::authorize('update', $profile);

        $validated = $this->validate([
            'professional_title' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:60'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'service_areas' => ['array', 'max:27'],
            'service_areas.*' => ['string', 'distinct', Rule::exists('governorates', 'name_ar')],
        ]);

        $validated['service_areas'] = array_values($validated['service_areas'] ?? []);

        // Optional fields arrive as empty strings from the form. Storing null
        // keeps "not provided" from looking like an edited license number.
        foreach (['professional_title', 'license_number', 'bio'] as $field) {
            $validated[$field] = filled($validated[$field] ?? null) ? $validated[$field] : null;
        }

        $profile->fill($validated);

        if ($profile->verification_status === VerificationStatus::Verified
            && $profile->isDirty('license_number')) {
            $profile->verification_status = VerificationStatus::Pending;
            $profile->verified_at = null;
            $profile->verified_by = null;
            $this->reverificationTriggered = true;
        }

        $profile->save();

        $this->savedMessage = __('app.account.broker.saved');
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var BrokerProfile|null $profile */
        $profile = $user->brokerProfile;

        return view('livewire.account.broker-details', [
            'profile' => $profile,
            'companyName' => $profile?->company?->company_name,
            'governorates' => Governorate::query()->where('is_active', true)->orderBy('name_ar')->get(),
        ]);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
