<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Infolists;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\HtmlEntry;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function formSchema(Schema $schema): Schema
    {
        return Schema::make([
            Card::make('معلومات العقار')
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
                        ->label('الحالة')
                        ->options([
                            'draft' => 'مسودة',
                            'pending' => 'قيد المراجعة',
                            'published' => 'منشور',
                            'rejected' => 'مرفوض',
                            'sold' => 'تم البيع',
                            'rented' => 'تم التأجير',
                            'archived' => 'مؤرشف',
                        ])
                        ->disabled(),

                    TextInput::make('price')
                        ->numeric()
                        ->prefix('ج.م ')
                        ->disabled(),

                    TextInput::make('area')
                        ->numeric()
                        ->label('المساحة (م²)')
                        ->disabled(),

                    Select::make('bedrooms')
                        ->options([
                            0 => 'غرفة واحدة',
                            1 => 'غرفتان',
                            2 => 'ثلاث غرف',
                            3 => 'أربع غرف أو أكثر',
                        ])
                        ->disabled(),

                    Select::make('bathrooms')
                        ->options([
                            1 => 'حمام واحد',
                            2 => 'حمامان',
                            3 => 'ثلاث حمامات أو أكثر',
                        ])
                        ->disabled(),

                    Select::make('finishing')
                        ->options([
                            'unfinished' => 'غير مشطب',
                            'semi_finished' => 'نصف مشطب',
                            'finished' => 'مشطب',
                            'furnished' => 'مفروش',
                        ])
                        ->disabled(),

                    TextInput::make('address')
                        ->label('العنوان')
                        ->disabled(),

                    Select::make('governorate_id')
                        ->relationship('governorate', 'name_ar')
                        ->disabled(),

                    Select::make('city_id')
                        ->relationship('city', 'name_ar')
                        ->disabled(),

                    Select::make('district_id')
                        ->relationship('district', 'name_ar')
                        ->disabled(),

                    TextInput::make('year_built')
                        ->numeric()
                        ->label('عام البناء')
                        ->disabled(),

                    TextInput::make('floor')
                        ->numeric()
                        ->label('الطابق')
                        ->disabled(),

                    TextInput::make('total_floors')
                        ->numeric()
                        ->label('إجمالي الطوابق')
                        ->disabled(),
                ]),

            Card::make('المرافق')
                ->schema([
                    // Amenities will be shown via Livewire or a simple list
                    HtmlEntry::make('amenities')
                        ->label('المرافق')
                        ->content('<!-- Amenities to be rendered via Livewire or component -->'),
                ]),

            Card::make('الصور')
                ->schema([
                    ImageEntry::make('coverImage')
                        ->label('صورة الغلاف'),

                    // Images count
                    TextEntry::make('images_count')
                        ->label('عدد الصور')
                        ->getStateUsing(fn (Property $record): int => $record->images->count()),

                    // Simple image thumbnails
                    HtmlEntry::make('image_thumbs')
                        ->label('معاينة الصور')
                        ->content('<!-- Image thumbnails -->'),
                ]),
        ]);
    }

    protected function infolistSchema(Schema $schema): Schema
    {
        return Schema::make([
            // Property details in infolist
            Section::make('معلومات العقار')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')
                        ->label('عنوان العقار'),

                    TextEntry::make('slug')
                        ->label('الرابط'),

                    TextEntry::make('purpose')
                        ->label('الغرض'),

                    TextEntry::make('status')
                        ->label('الحالة'),

                    TextEntry::make('price')
                        ->label('السعر'),

                    TextEntry::make('area')
                        ->label('المساحة'),

                    TextEntry::make('bedrooms')
                        ->label('الغرف'),

                    TextEntry::make('bathrooms')
                        ->label('الحمامات'),
                ]),

            Section::make('الموقع')
                ->columns(2)
                ->schema([
                    TextEntry::make('governorate_id')
                        ->label('المحافظة'),

                    TextEntry::make('city_id')
                        ->label('المدينة'),

                    TextEntry::make('district_id')
                        ->label('المنطقة'),
                ]),
        ]);
    }
}