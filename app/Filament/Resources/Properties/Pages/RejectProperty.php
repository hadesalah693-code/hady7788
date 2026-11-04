<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class RejectProperty extends EditRecord
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
            Card::make('رفض العقار')
                ->schema([
                    TextInput::make('title')
                        ->label('عنوان العقار')
                        ->disabled(),

                    Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->required()
                        ->columnSpanFull()
                        ->maxLength(500)
                        ->placeholder('اكتب سبب الرفض هنا...'),

                    Select::make('status')
                        ->label('الحالة الجديدة')
                        ->options([
                            'rejected' => 'مرفوض',
                        ])
                        ->default('rejected')
                        ->disabled(),
                ]),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم رفض العقار';
    }

    protected function getSavedNotificationBody(): ?string
    {
        return 'تم رفض العقار بنجاح وسيتم إعلام المعلن بالسبب.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = PropertyStatus::Rejected->value;

        return $data;
    }
}