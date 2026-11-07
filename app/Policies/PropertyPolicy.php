<?php

namespace App\Policies;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Property $property): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (
            $property->status === PropertyStatus::Published
            && $property->published_at !== null
            && $property->published_at->lte(now())
        ) {
            return true;
        }

        return $user->is($property->user);
    }

    public function create(User $user): bool
    {
        return $user->isSuspended() === false;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->isAdmin() || $user->is($property->user);
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->isAdmin() || $user->is($property->user);
    }

    public function restore(User $user, Property $property): bool
    {
        return $user->isAdmin() || $user->is($property->user);
    }

    public function forceDelete(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, Property $property): bool
    {
        return $user->isAdmin() || ($user->is($property->user) && $user->canPublishProperties());
    }
}
