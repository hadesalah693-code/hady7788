<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use Filament\Pages\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;

class ListProperties extends ListRecords
{
    protected static string $resource = \App\Filament\Resources\Properties\PropertyResource::class;

    protected function getTableProps(): array
    {
        return [
            'fullWidth' => true,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('إضافة عقار')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedPlus)
                ->url('/admin/properties/create'),
        ];
    }
}