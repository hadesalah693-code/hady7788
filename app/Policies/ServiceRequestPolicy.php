<?php

namespace App\Policies;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin()
            || $request->isOwnedBy($user)
            || $request->isAssignedTo($user);
    }

    public function create(User $user): bool
    {
        return ! $user->isSuspended();
    }

    public function update(User $user, ServiceRequest $request): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $request->isOwnedBy($user)
            && in_array($request->status, [ServiceRequestStatus::New, ServiceRequestStatus::UnderReview], true);
    }

    public function delete(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, ServiceRequest $request): bool
    {
        return $request->status->isOpen()
            && ($user->isAdmin() || $request->isOwnedBy($user));
    }

    public function assign(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin() && $request->status->isOpen();
    }

    public function manageStatus(User $user, ServiceRequest $request): bool
    {
        if ($request->status->isFinal()) {
            return false;
        }

        return $user->isAdmin() || $request->isAssignedTo($user);
    }

    public function review(User $user, ServiceRequest $request): bool
    {
        return $request->canBeReviewedBy($user);
    }
}
