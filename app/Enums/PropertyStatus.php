<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Sold = 'sold';
    case Rented = 'rented';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Pending => 'قيد المراجعة',
            self::Published => 'منشور',
            self::Rejected => 'مرفوض',
            self::Sold => 'تم البيع',
            self::Rented => 'تم التأجير',
            self::Archived => 'مؤرشف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Pending => 'warning',
            self::Published => 'success',
            self::Rejected => 'danger',
            self::Sold, self::Rented => 'gray',
            self::Archived => 'gray',
        };
    }
}
