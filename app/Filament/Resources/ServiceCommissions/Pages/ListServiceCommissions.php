<?php

namespace App\Filament\Resources\ServiceCommissions\Pages;

use App\Filament\Resources\ServiceCommissions\ServiceCommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceCommissions extends ListRecords
{
    protected static string $resource = ServiceCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
