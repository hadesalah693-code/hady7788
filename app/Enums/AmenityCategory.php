<?php

namespace App\Enums;

enum AmenityCategory: string
{
    case Indoor = 'indoor';
    case Building = 'building';
    case Extra = 'extra';

    public function label(): string
    {
        return match ($this) {
            self::Indoor => 'داخل العقار',
            self::Building => 'مرافق المبنى',
            self::Extra => 'مرافق إضافية',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Indoor => 'heroicon-o-home-modern',
            self::Building => 'heroicon-o-building-office-2',
            self::Extra => 'heroicon-o-sparkles',
        };
    }
}
