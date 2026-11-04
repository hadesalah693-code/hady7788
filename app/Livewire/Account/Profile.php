<?php

namespace App\Livewire\Account;

use App\Models\City;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.account')]
#[Title('الملف الشخصي')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $bio = '';

    public ?int $governorate_id = null;

    public ?int $city_id = null;

    /** @var TemporaryUploadedFile|null */
    public $avatar = null;

    public ?string $currentAvatar = null;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $user = $this->currentUser();

        Gate::authorize('update', $user);

        $this->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'whatsapp' => $user->whatsapp ?? '',
            'bio' => $user->bio ?? '',
            'governorate_id' => $user->governorate_id,
            'city_id' => $user->city_id,
        ]);

        $this->currentAvatar = $user->avatar_path;
    }

    public function updatedGovernorateId(): void
    {
        $this->city_id = null;
    }

    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar', ['avatar' => $this->avatarRules()]);
    }

    public function removeAvatar(): void
    {
        $user = $this->currentUser();

        Gate::authorize('update', $user);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        $this->currentAvatar = null;
        $this->reset('avatar');
        $this->savedMessage = __('app.account.profile.saved');
    }

    public function save(): void
    {
        $user = $this->currentUser();

        Gate::authorize('update', $user);

        $this->email = Str::lower(trim($this->email));
        $this->phone = $this->normalizePhone($this->phone);
        $this->whatsapp = $this->normalizePhone($this->whatsapp);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9]{8,15}$/',
                Rule::unique('users', 'phone')->ignore($user->getKey()),
            ],
            'whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'governorate_id' => ['nullable', 'integer', Rule::exists('governorates', 'id')],
            'city_id' => [
                'nullable',
                'integer',
                Rule::exists('cities', 'id')->where('governorate_id', $this->governorate_id),
            ],
            'avatar' => $this->avatarRules(),
        ]);

        unset($validated['avatar']);

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $validated['avatar_path'] = $path;
        }

        $user->update($validated);

        $this->currentAvatar = $user->avatar_path;
        $this->reset('avatar');
        $this->savedMessage = __('app.account.profile.saved');
    }

    public function render(): View
    {
        return view('livewire.account.profile', [
            'governorates' => Governorate::query()->where('is_active', true)->orderBy('name_ar')->get(),
            'cities' => $this->governorate_id
                ? City::query()->where('governorate_id', $this->governorate_id)->orderBy('name_ar')->get()
                : collect(),
        ]);
    }

    /**
     * The authenticated account. Account pages never accept a user identifier
     * from the request, so a user can only ever edit their own record.
     */
    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    /**
     * @return array<int, mixed>
     */
    private function avatarRules(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
            'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
        ];
    }

    private function normalizePhone(string $value): string
    {
        return (string) preg_replace('/[\s\-()]/', '', trim($value));
    }
}
