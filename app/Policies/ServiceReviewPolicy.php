<?php

namespace App\Policies;

use App\Models\ServiceReview;
use App\Models\User;

class ServiceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceReview $review): bool
    {
        return $user->isAdmin()
            || (int) $review->user_id === (int) $user->getKey()
            || (int) $review->provider?->user_id === (int) $user->getKey();
    }

    public function create(User $user): bool
    {
        return ! $user->isSuspended();
    }

    public function report(User $user, ServiceReview $review): bool
    {
        return $user->isAdmin() || (int) $review->user_id === (int) $user->getKey();
    }

    public function delete(User $user, ServiceReview $review): bool
    {
        return $user->isAdmin();
    }
}
