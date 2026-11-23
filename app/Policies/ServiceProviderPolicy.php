<?php

namespace App\Policies;

use App\Models\ServiceProvider;
use App\Models\User;

class ServiceProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceProvider $provider): bool
    {
        return $user->isAdmin()
            || $provider->isActive()
            || (int) $provider->user_id === (int) $user->getKey();
    }

    public function create(User $user): bool
    {
        return ! $user->isSuspended() && $user->serviceProvider()->doesntExist();
    }

    public function update(User $user, ServiceProvider $provider): bool
    {
        return $user->isAdmin() || (int) $provider->user_id === (int) $user->getKey();
    }

    public function approve(User $user, ServiceProvider $provider): bool
    {
        return $user->isAdmin();
    }

    public function suspend(User $user, ServiceProvider $provider): bool
    {
        return $user->isAdmin();
    }

    public function receiveRequests(User $user, ServiceProvider $provider): bool
    {
        return (int) $provider->user_id === (int) $user->getKey()
            && $provider->canReceiveRequests();
    }
}
