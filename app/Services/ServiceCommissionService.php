<?php

namespace App\Services;

use App\Enums\CommissionStatus;
use App\Enums\ServiceRequestStatus;
use App\Models\CommissionSetting;
use App\Models\ServiceCommission;
use App\Models\ServiceRequest;

class ServiceCommissionService
{
    /**
     * Create or refresh the commission row for a completed request.
     */
    public function syncFor(ServiceRequest $request): ?ServiceCommission
    {
        if ($request->status !== ServiceRequestStatus::Completed) {
            return null;
        }

        $setting = CommissionSetting::current();
        $value = $request->quoted_price !== null ? (float) $request->quoted_price : null;
        $calculation = $setting->calculate($value);

        return ServiceCommission::updateOrCreate(
            ['service_request_id' => $request->getKey()],
            [
                'service_provider_id' => $request->service_provider_id,
                'type' => $calculation['type'],
                'rate' => $calculation['rate'],
                'amount' => $calculation['amount'],
                'status' => CommissionStatus::Pending,
            ],
        );
    }

    public function settle(ServiceCommission $commission): ServiceCommission
    {
        $commission->forceFill([
            'status' => CommissionStatus::Settled,
            'settled_at' => now(),
        ])->save();

        return $commission;
    }

    public function waive(ServiceCommission $commission): ServiceCommission
    {
        $commission->forceFill([
            'status' => CommissionStatus::Waived,
            'settled_at' => null,
        ])->save();

        return $commission;
    }
}
