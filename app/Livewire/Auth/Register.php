<?php

namespace App\Livewire\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('إنشاء حساب')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $account_type = 'user';

    public string $company_name = '';

    public bool $terms = false;

    public function register(): void
    {
        $this->email = Str::lower(trim($this->email));
        $this->phone = trim($this->phone);

        $throttleKey = 'register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($throttleKey)]),
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique(User::class)],
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'company_name' => ['exclude_unless:account_type,company', 'required', 'string', 'min:2', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $accountType = AccountType::from($validated['account_type']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'password' => $validated['password'],
            'account_type' => $accountType,
            'status' => $accountType->requiresVerification()
                ? AccountStatus::Pending
                : AccountStatus::Active,
            'is_admin' => false,
        ]);

        if ($accountType === AccountType::Broker) {
            $user->brokerProfile()->create([
                'verification_status' => VerificationStatus::Pending,
            ]);
        }

        if ($accountType === AccountType::Company) {
            $user->companyProfile()->create([
                'company_name' => $validated['company_name'],
                'verification_status' => VerificationStatus::Pending,
            ]);
        }

        Auth::login($user);
        session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        session()->flash('status', __('app.auth.registered', ['name' => $user->name]));

        $this->redirect(route('home'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register', [
            'accountTypes' => AccountType::cases(),
        ]);
    }
}
