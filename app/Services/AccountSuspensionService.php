<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountSuspensionService
{
    /**
     * Suspend an account, revoke its remember-me token and destroy every stored session.
     */
    public function suspend(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->forceFill([
                'status' => AccountStatus::Suspended,
                'remember_token' => null,
            ])->save();

            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        });
    }

    /**
     * Restore access. Brokers and companies return to "pending" until their profile is verified.
     */
    public function reactivate(User $user): void
    {
        $isVerified = $user->isRegularUser()
            || $user->profileVerificationStatus()?->isVerified() === true;

        $user->forceFill([
            'status' => $isVerified ? AccountStatus::Active : AccountStatus::Pending,
        ])->save();
    }
}
