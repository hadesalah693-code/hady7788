<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTechnicianProfile extends CreateRecord
{
    protected static string $resource = TechnicianProfileResource::class;
}
