<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class ArchiveProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('عودة')
                ->icon(Heroicon::OutlineArrowLeft)
                ->url('/admin/properties'),
        ];
    }

    protected function formSchema(Schema $schema): Schema
    {
        return Schema::make([
            Card::make('ت archivng العقار')
                ->schema([
                    TextInput::make('title')
                        ->label('عنوان العقار')
                        ->disabled(),

                    Select::make('status')
                        ->label('الحالة الجديدة')
                        ->options([
                            'archived' => 'مؤرشف',
                        ])
                        ->default('archived')
                        ->disabled(),

                    TextInput::make('reason')
                        ->label('سبب الأرشفة')
                        ->placeholder('اختياري')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم أرشفة العقار';
    }

    protected function getSavedNotificationBody(): ?string
    {
        return 'تم أرشفة العقار successfully.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = PropertyStatus::Archived->value;

        return $data;
    }
}