<?php

namespace App\Enums;

enum ServiceRequestStatus: string
{
    case New = 'new';
    case UnderReview = 'under_review';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::UnderReview => 'قيد المراجعة',
            self::Assigned => 'تم الإسناد لمقدم خدمة',
            self::Accepted => 'تم قبول الطلب',
            self::InProgress => 'جاري التنفيذ',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::UnderReview => 'warning',
            self::Assigned => 'info',
            self::Accepted => 'success',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'heroicon-o-sparkles',
            self::UnderReview => 'heroicon-o-magnifying-glass',
            self::Assigned => 'heroicon-o-user-plus',
            self::Accepted => 'heroicon-o-check-badge',
            self::InProgress => 'heroicon-o-wrench-screwdriver',
            self::Completed => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::New => [self::UnderReview, self::Assigned, self::Cancelled],
            self::UnderReview => [self::Assigned, self::Cancelled],
            self::Assigned => [self::Accepted, self::New, self::Cancelled],
            self::Accepted => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedNext(), true);
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function isFinal(): bool
    {
        return ! $this->isOpen();
    }
}
