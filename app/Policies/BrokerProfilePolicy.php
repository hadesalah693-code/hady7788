<?php

namespace App\Policies;

use App\Models\BrokerProfile;
use App\Models\User;

class BrokerProfilePolicy
{
    public function view(User $actor, BrokerProfile $profile): bool
    {
        return $actor->isAdmin() || $actor->id === $profile->user_id;
    }

    public function update(User $actor, BrokerProfile $profile): bool
    {
        return $actor->isAdmin() || $actor->id === $profile->user_id;
    }
}
