<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianProfiles extends ListRecords
{
    protected static string $resource = TechnicianProfileResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
