<?php

namespace App\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All service request status changes pass through this class so timestamps,
 * provider counters and commissions stay consistent.
 */
class ServiceRequestWorkflow
{
    public function __construct(private readonly ServiceCommissionService $commissions) {}

    public function assign(ServiceRequest $request, ServiceProvider $provider, User $actor): ServiceRequest
    {
        $this->ensureAdmin($actor);

        if (! $provider->canReceiveRequests()) {
            throw ValidationException::withMessages([
                'provider' => 'مقدم الخدمة غير نشط أو غير متاح حاليًا.',
            ]);
        }

        return DB::transaction(function () use ($request, $provider): ServiceRequest {
            $locked = $this->lock($request);

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages(['status' => 'لا يمكن إسناد طلب مكتمل أو ملغي.']);
            }

            $locked->forceFill([
                'service_provider_id' => $provider->getKey(),
                'status' => ServiceRequestStatus::Assigned,
                'assigned_at' => now(),
                'accepted_at' => null,
                'started_at' => null,
                'cancellation_reason' => null,
            ])->save();

            return $locked;
        });
    }

    public function accept(ServiceRequest $request, User $actor): ServiceRequest
    {
        $this->ensureAssignedProviderOrAdmin($request, $actor);

        return $this->transition($request, ServiceRequestStatus::Accepted, $actor);
    }

    public function reject(ServiceRequest $request, User $actor, ?string $reason = null): ServiceRequest
    {
        $this->ensureAssignedProviderOrAdmin($request, $actor);

        return DB::transaction(function () use ($request, $reason): ServiceRequest {
            $locked = $this->lock($request);

            if ($locked->status !== ServiceRequestStatus::Assigned) {
                throw ValidationException::withMessages(['status' => 'لا يمكن رفض الطلب في حالته الحالية.']);
            }

            $locked->forceFill([
                'service_provider_id' => null,
                'status' => ServiceRequestStatus::New,
                'assigned_at' => null,
                'accepted_at' => null,
                'started_at' => null,
                'admin_notes' => trim(($locked->admin_notes ?? '')."\nرفض مقدم الخدمة: ".($reason ?: 'بدون سبب مذكور')),
            ])->save();

            return $locked;
        });
    }

    public function start(ServiceRequest $request, User $actor): ServiceRequest
    {
        $this->ensureAssignedProviderOrAdmin($request, $actor);

        return $this->transition($request, ServiceRequestStatus::InProgress, $actor);
    }

    public function complete(ServiceRequest $request, User $actor): ServiceRequest
    {
        $this->ensureAssignedProviderOrAdmin($request, $actor);

        return $this->transition($request, ServiceRequestStatus::Completed, $actor);
    }

    public function cancel(ServiceRequest $request, User $actor, ?string $reason = null): ServiceRequest
    {
        if (! $actor->isAdmin() && ! $request->isOwnedBy($actor)) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($request, $reason): ServiceRequest {
            $locked = $this->lock($request);

            if ($locked->status->isFinal()) {
                throw ValidationException::withMessages(['status' => 'الطلب مغلق بالفعل.']);
            }

            $locked->forceFill([
                'status' => ServiceRequestStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ])->save();

            return $locked;
        });
    }

    public function transition(ServiceRequest $request, ServiceRequestStatus $status, ?User $actor = null): ServiceRequest
    {
        return DB::transaction(function () use ($request, $status): ServiceRequest {
            $locked = $this->lock($request);

            if (! $locked->status->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => 'انتقال غير مسموح من "'.$locked->status->label().'" إلى "'.$status->label().'".',
                ]);
            }

            $attributes = ['status' => $status];

            $attributes += match ($status) {
                ServiceRequestStatus::Assigned => ['assigned_at' => now()],
                ServiceRequestStatus::Accepted => ['accepted_at' => now()],
                ServiceRequestStatus::InProgress => ['started_at' => now()],
                ServiceRequestStatus::Completed => ['completed_at' => now()],
                ServiceRequestStatus::Cancelled => ['cancelled_at' => now()],
                default => [],
            };

            $locked->forceFill($attributes)->save();

            if ($status === ServiceRequestStatus::Completed) {
                $this->commissions->syncFor($locked);

                if ($locked->provider !== null) {
                    $locked->provider->increment('completed_requests_count');
                }
            }

            return $locked->refresh();
        });
    }

    private function lock(ServiceRequest $request): ServiceRequest
    {
        return ServiceRequest::query()->lockForUpdate()->findOrFail($request->getKey());
    }

    private function ensureAdmin(User $actor): void
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException;
        }
    }

    private function ensureAssignedProviderOrAdmin(ServiceRequest $request, User $actor): void
    {
        if (! $actor->isAdmin() && ! $request->isAssignedTo($actor)) {
            throw new AuthorizationException;
        }
    }
}
