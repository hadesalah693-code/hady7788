<?php

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending = 'pending';
    case Settled = 'settled';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'مستحقة',
            self::Settled => 'تم التحصيل',
            self::Waived => 'معفاة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Settled => 'success',
            self::Waived => 'gray',
        };
    }
}
