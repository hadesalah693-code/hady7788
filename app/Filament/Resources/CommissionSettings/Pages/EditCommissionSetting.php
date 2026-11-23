<?php

namespace App\Filament\Resources\CommissionSettings\Pages;

use App\Filament\Resources\CommissionSettings\CommissionSettingResource;
use App\Models\CommissionSetting;
use Filament\Resources\Pages\EditRecord;

class EditCommissionSetting extends EditRecord
{
    protected static string $resource = CommissionSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if (! $record->is_active) {
            return;
        }

        CommissionSetting::query()
            ->whereKeyNot($record->getKey())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
