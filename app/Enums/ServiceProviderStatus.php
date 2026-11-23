<?php

namespace App\Enums;

enum ServiceProviderStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Active => 'نشط',
            self::Rejected => 'مرفوض',
            self::Suspended => 'موقوف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Rejected => 'danger',
            self::Suspended => 'danger',
        };
    }

    public function canReceiveRequests(): bool
    {
        return $this === self::Active;
    }
}
