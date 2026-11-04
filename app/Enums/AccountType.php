<?php

namespace App\Enums;

enum AccountType: string
{
    case User = 'user';
    case Broker = 'broker';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::User => 'مستخدم عادي',
            self::Broker => 'وسيط عقاري',
            self::Company => 'شركة عقارية',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::User => 'ابحث عن العقارات وتواصل مع المعلنين.',
            self::Broker => 'اعرض عقاراتك واحصل على عملاء جدد.',
            self::Company => 'أدر إعلانات شركتك ووسطائها من مكان واحد.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::User => 'gray',
            self::Broker => 'info',
            self::Company => 'warning',
        };
    }

    public function iconComponent(): string
    {
        return match ($this) {
            self::User => 'heroicon-o-user',
            self::Broker => 'heroicon-o-briefcase',
            self::Company => 'heroicon-o-building-office-2',
        };
    }

    public function requiresVerification(): bool
    {
        return $this !== self::User;
    }
}
