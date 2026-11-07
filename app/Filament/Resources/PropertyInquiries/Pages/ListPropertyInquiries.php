<?php

namespace App\Filament\Resources\PropertyInquiries\Pages;

use App\Filament\Resources\PropertyInquiries\PropertyInquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListPropertyInquiries extends ListRecords
{
    protected static string $resource = PropertyInquiryResource::class;
}
