<?php

namespace App\Policies;

use App\Models\CompanyProfile;
use App\Models\User;

class CompanyProfilePolicy
{
    public function view(User $actor, CompanyProfile $profile): bool
    {
        return $actor->isAdmin() || $actor->id === $profile->user_id;
    }

    public function update(User $actor, CompanyProfile $profile): bool
    {
        return $actor->isAdmin() || $actor->id === $profile->user_id;
    }
}
