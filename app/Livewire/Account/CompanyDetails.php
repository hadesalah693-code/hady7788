<?php

namespace App\Livewire\Account;

use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.account')]
#[Title('بيانات الشركة')]
class CompanyDetails extends Component
{
    use WithFileUploads;

    public string $company_name = '';

    public string $commercial_register = '';

    public string $tax_id = '';

    public string $website = '';

    public string $address = '';

    public string $description = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    public ?string $currentLogo = null;

    public ?string $savedMessage = null;

    public bool $reverificationTriggered = false;

    public function mount(): void
    {
        $user = $this->currentUser();

        abort_unless($user->isCompany(), 403);

        $profile = $user->companyProfile;

        if ($profile) {
            Gate::authorize('update', $profile);

            $this->fill([
                'company_name' => $profile->company_name,
                'commercial_register' => $profile->commercial_register ?? '',
                'tax_id' => $profile->tax_id ?? '',
                'website' => $profile->website ?? '',
                'address' => $profile->address ?? '',
                'description' => $profile->description ?? '',
            ]);

            $this->currentLogo = $profile->logo_path;
        } else {
            $this->company_name = $user->name;
        }
    }

    public function updatedLogo(): void
    {
        $this->validateOnly('logo', ['logo' => $this->logoRules()]);
    }

    public function removeLogo(): void
    {
        $user = $this->currentUser();

        abort_unless($user->isCompany(), 403);

        $profile = $user->companyProfile;

        if ($profile?->logo_path) {
            Gate::authorize('update', $profile);

            Storage::disk('public')->delete($profile->logo_path);
            $profile->update(['logo_path' => null]);
        }

        $this->currentLogo = null;
        $this->reset('logo');
        $this->savedMessage = __('app.account.company.saved');
    }

    public function save(): void
    {
        $user = $this->currentUser();

        abort_unless($user->isCompany(), 403);

        $profile = $user->companyProfile()->firstOrCreate([], [
            'company_name' => $this->company_name !== '' ? $this->company_name : $user->name,
        ]);

        Gate::authorize('update', $profile);

        $this->website = trim($this->website);

        $validated = $this->validate([
            'company_name' => ['required', 'string', 'min:2', 'max:150'],
            'commercial_register' => ['nullable', 'string', 'max:60'],
            'tax_id' => ['nullable', 'string', 'max:60'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => $this->logoRules(),
        ]);

        unset($validated['logo']);

        // Optional fields arrive as empty strings from the form. Storing null
        // keeps "not provided" from looking like an edited official value.
        foreach (['commercial_register', 'tax_id', 'website', 'address', 'description'] as $field) {
            $validated[$field] = filled($validated[$field] ?? null) ? $validated[$field] : null;
        }

        if ($this->logo) {
            $path = $this->logo->store('company-logos', 'public');

            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            $validated['logo_path'] = $path;
        }

        $profile->fill($validated);

        // Official identity fields are protected: changing them after approval
        // sends the profile back to review instead of keeping the verified badge.
        if ($profile->verification_status === VerificationStatus::Verified
            && $profile->isDirty(['company_name', 'commercial_register', 'tax_id'])) {
            $profile->verification_status = VerificationStatus::Pending;
            $profile->verified_at = null;
            $profile->verified_by = null;
            $this->reverificationTriggered = true;
        }

        $profile->save();

        $this->currentLogo = $profile->logo_path;
        $this->reset('logo');
        $this->savedMessage = __('app.account.company.saved');
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('livewire.account.company-details', [
            'profile' => $user->companyProfile,
            'brokersCount' => $user->companyProfile?->brokers()->count() ?? 0,
        ]);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    /**
     * @return array<int, mixed>
     */
    private function logoRules(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
            'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
        ];
    }
}
