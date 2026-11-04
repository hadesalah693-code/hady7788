<?php

namespace App\Providers;

use App\Models\BrokerProfile;
use App\Models\CompanyProfile;
use App\Models\Property;
use App\Models\User;
use App\Policies\BrokerProfilePolicy;
use App\Policies\CompanyProfilePolicy;
use App\Policies\PropertyPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(BrokerProfile::class, BrokerProfilePolicy::class);
        Gate::policy(CompanyProfile::class, CompanyProfilePolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);

        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());

        Gate::define('verify-accounts', fn (User $user): bool => $user->isAdmin());

        Gate::define('publish-properties', fn (User $user): bool => $user->canPublishProperties());

        Password::defaults(fn (): Password => Password::min(8)->letters()->numbers());
    }
}
