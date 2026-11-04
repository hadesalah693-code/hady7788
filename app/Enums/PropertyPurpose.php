<?php

namespace App\Enums;

enum PropertyPurpose: string
{
    case Sale = 'sale';
    case Rent = 'rent';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'للبيع',
            self::Rent => 'للإيجار',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Sale => 'bg-brand-600 text-white',
            self::Rent => 'bg-amber-500 text-white',
        };
    }
}
