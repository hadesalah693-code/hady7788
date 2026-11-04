<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Pending => 'قيد التوثيق',
            self::Suspended => 'موقوف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Pending => 'warning',
            self::Suspended => 'danger',
        };
    }

    public function canLogin(): bool
    {
        return $this !== self::Suspended;
    }
}
