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
use Filament\Actions\Action;

class ApproveProperty extends EditRecord
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
            Card::make('تفاصيل العقار')
                ->schema([
                    TextInput::make('title')
                        ->label('عنوان العقار')
                        ->disabled(),

                    Select::make('purpose')
                        ->label('الغرض')
                        ->options([
                            'sale' => 'للبيع',
                            'rent' => 'للإيجار',
                        ])
                        ->disabled(),

                    Select::make('status')
                        ->label('الحالة الجديدة')
                        ->options([
                            'published' => 'منشور',
                        ])
                        ->default('published')
                        ->reactive(),

                    Select::make('governorate_id')
                        ->relationship('governorate', 'name_ar')
                        ->disabled(),

                    Select::make('city_id')
                        ->relationship('city', 'name_ar')
                        ->disabled(),

                    TextInput::make('price')
                        ->numeric()
                        ->prefix('ج.م ')
                        ->disabled(),

                    TextInput::make('area')
                        ->numeric()
                        ->label('المساحة (م²)')
                        ->disabled(),

                    TextInput::make('address')
                        ->label('العنوان')
                        ->disabled(),

                    Textarea::make(' rejection_reason')
                        ->label('سبب الرفض')
                        ->columnSpanFull()
                        ->disable(),
                ]),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تمت الموافقة على العقار';
    }

    protected function getSavedNotificationBody(): ?string
    {
        return 'تمت الموافقة على العقار ونشره بنجاح.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = PropertyStatus::Published->value;
        $data['published_at'] = now();
        $data['published_by'] = auth()->id();

        return $data;
    }
}