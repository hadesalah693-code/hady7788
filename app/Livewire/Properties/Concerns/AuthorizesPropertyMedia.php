<?php

namespace App\Livewire\Properties\Concerns;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Shared ownership and state rules for the media/amenities managers.
 *
 * Owners may manage media in every state except sold/rented. Administrators
 * keep full access through PropertyPolicy. Every entry point authorizes the
 * property, so a forged property id can never reach another user's listing.
 */
trait AuthorizesPropertyMedia
{
    public int $propertyId;

    protected function mediaProperty(): Property
    {
        $this->mediaUser();

        $property = Property::query()->findOrFail($this->propertyId);

        Gate::authorize('update', $property);

        return $property;
    }

    protected function mediaUser(): User
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_if($user === null || $user->isSuspended(), 403);

        return $user;
    }

    protected function canManage(Property $property): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null || $user->isSuspended()) {
            return false;
        }

        return $user->isAdmin()
            || ! in_array($property->status, [PropertyStatus::Sold, PropertyStatus::Rented], true);
    }

    protected function assertCanManage(Property $property): void
    {
        abort_unless($this->canManage($property), 403);
    }
}
