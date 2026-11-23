<?php

namespace App\Enums;

enum CommissionType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'مبلغ ثابت لكل طلب',
            self::Percentage => 'نسبة مئوية من قيمة الخدمة',
        };
    }
}
