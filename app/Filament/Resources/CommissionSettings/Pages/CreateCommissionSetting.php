<?php

namespace App\Filament\Resources\CommissionSettings\Pages;

use App\Filament\Resources\CommissionSettings\CommissionSettingResource;
use App\Models\CommissionSetting;
use Filament\Resources\Pages\CreateRecord;

class CreateCommissionSetting extends CreateRecord
{
    protected static string $resource = CommissionSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->deactivateOthers();
    }

    private function deactivateOthers(): void
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
