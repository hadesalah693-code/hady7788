<?php

namespace App\Livewire\Account;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('الأمان وكلمة المرور')]
class Security extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $savedMessage = null;

    public function updatePassword(): void
    {
        /** @var User $user */
        $user = auth()->user();

        Gate::authorize('update', $user);

        $throttleKey = 'password-update:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('current_password', __('app.account.security.throttled', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::defaults(),
            ],
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
        }

        $user->update(['password' => $this->password]);

        // Regenerate the remember token so any previously issued "remember me"
        // cookie on another device can no longer authenticate the account.
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        // Invalidate authentication on every other device/browser. This updates
        // the password hash stored in their session so AuthenticateSession
        // rejects them on the next request.
        Auth::logoutOtherDevices($this->password);

        // Remove the other session records from the database, keeping only the
        // session that is performing this change.
        DB::table('sessions')
            ->where('user_id', $user->getKey())
            ->where('id', '!=', session()->getId())
            ->delete();

        RateLimiter::clear($throttleKey);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->savedMessage = __('app.account.security.saved');

        session()->regenerate();
    }

    public function render(): View
    {
        return view('livewire.account.security');
    }
}
