<?php

namespace App\Filament\Resources\Properties;

use App\Enums\PropertyStatus;
use App\Http\Controllers\PropertyController;
use App\Livewire\Properties\PropertyImages;
use App\Livewire\Properties\PropertyAmenities;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Infolists;
use Filament\Infolists\Components\Icon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationLabel = 'العقارات';

    protected static ?string $modelLabel = 'عقار';

    protected static ?string $pluralModelLabel = 'العقارات';

    public static function form(Schema $schema): Schema
    {
        return Schema::make([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->label('عنوان العقار'),

            Textarea::make('description')
                ->required()
                ->columnSpanFull()
                ->label('الوصف'),

            Select::make('property_type_id')
                ->relationship('propertyType', 'name')
                ->nullable()
                ->label('نوع العقار'),

            Select::make('purpose')
                ->options([
                    'sale' => 'للبيع',
                    'rent' => 'للإيجار',
                ])
                ->default('sale')
                ->label('الغرض'),

            Select::make('governorate_id')
                ->relationship('governorate', 'name_ar')
                ->nullable()
                ->label('المحافظة'),

            Select::make('city_id')
                ->relationship('city', 'name_ar')
                ->nullable()
                ->label('المدينة'),

            Select::make('district_id')
                ->relationship('district', 'name_ar')
                ->nullable()
                ->label('المنطقة'),

            TextInput::make('price')
                ->numeric()
                ->prefix('ج.م ')
                ->label('السعر'),

            TextInput::make('area')
                ->numeric()
                ->label('المساحة (م²)'),

            Select::make('bedrooms')
                ->options([
                    0 => 'غرفة واحدة',
                    1 => 'غرفتان',
                    2 => 'ثلاث غرف',
                    3 => 'أربع غرف أو أكثر',
                ])
                ->label('عدد الغرف'),

            Select::make('bathrooms')
                ->options([
                    1 => 'حمام واحد',
                    2 => 'حمامان',
                    3 => 'ثلاث حمامات أو أكثر',
                ])
                ->label('عدد الحمامات'),

            Select::make('finishing')
                ->options([
                    'unfinished' => 'غير مشطب',
                    'semi_finished' => 'نصف مشطب',
                    'finished' => 'مشطب',
                    'furnished' => 'مفروش',
                ])
                ->label('التشطيب'),

            TextInput::make('year_built')
                ->numeric()
                ->label('عام البناء'),

            TextInput::make('floor')
                ->numeric()
                ->label('رقم الطابق'),

            TextInput::make('total_floors')
                ->numeric()
                ->label('إجمالي الطوابق'),

            TextInput::make('parking_spaces')
                ->numeric()
                ->label('عدد أماكن parking'),

            Select::make('status')
                ->options([
                    PropertyStatus::Draft->value => 'مسودة',
                    PropertyStatus::Pending->value => 'قيد المراجعة',
                    PropertyStatus::Published->value => 'منشور',
                    PropertyStatus::Rejected->value => 'مرفوض',
                    PropertyStatus::Sold->value => 'تم البيع',
                    PropertyStatus::Rented->value => 'تم التأجير',
                    PropertyStatus::Archived->value => 'مؤرشف',
                ])
                ->default(PropertyStatus::Draft->value)
                ->label('الحالة'),

            TextInput::make('available_from')
                ->label('متاح من'),

            TextInput::make('contact_name')
                ->label('اسم جهة الاتصال'),

            TextInput::make('contact_phone')
                ->tel()
                ->label('رقم الهاتف'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return Schema::make([
            InfolistSection::make('基本信息')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')
                        ->label('عنوان العقار'),

                    TextEntry::make('slug')
                        ->label('الرابط المختصر'),

                    TextEntry::make('purpose')
                        ->label('الغرض'),

                    TextEntry::make('status')
                        ->label('الحالة'),

                    TextEntry::make('governorate_id')
                        ->label('المحافظة'),

                    TextEntry::make('city_id')
                        ->label('المدينة'),

                    TextEntry::make('district_id')
                        ->label('المنطقة'),

                    TextEntry::make('property_type_id')
                        ->label('نوع العقار'),

                    TextEntry::make('price')
                        ->label('السعر'),

                    TextEntry::make('area')
                        ->label('المساحة'),

                    TextEntry::make('bedrooms')
                        ->label('الغرف'),

                    TextEntry::make('bathrooms')
                        ->label('الحمامات'),

                    TextEntry::make('finishing')
                        ->label('التشطيب'),

                    TextEntry::make('year_built')
                        ->label('عام البناء'),

                    TextEntry::make('floor')
                        ->label('الطابق'),

                    TextEntry::make('total_floors')
                        ->label('إجمالي الطوابق'),

                    TextEntry::make('parking_spaces')
                        ->label('أماكن parking'),

                    TextEntry::make('available_from')
                        ->label('متاح من'),

                    TextEntry::make('contact_name')
                        ->label('اسم جهة الاتصال'),

                    TextEntry::make('contact_phone')
                        ->label('رقم الهاتف'),
                ]),

            InfolistSection::make('وسائل التواصل')
                ->schema([
                    ImageEntry::make('coverImage')
                        ->label('صورة الغلاف'),

                    TextEntry::make('images_count')
                        ->label('عدد الصور')
                        ->getStateUsing(fn (Property $record): int => $record->images->count()),

                    TextEntry::make('amenities_count')
                        ->label('عدد المرافق')
                        ->getStateUsing(fn (Property $record): int => $record->amenities->count()),
                ]),

            InfolistSection::make('تواريخ المهمة')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء'),

                    TextEntry::make('updated_at')
                        ->label('آخر تعديل'),

                    TextEntry::make('published_at')
                        ->label('تاريخ النشر'),

                    TextEntry::make('published_by')
                        ->label('نشر بواسطة'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Properties\Pages\ListProperties::route('/'),
            'create' => \App\Filament\Resources\Properties\Pages\CreateProperty::route('/create'),
            'view' => \App\Filament\Resources\Properties\Pages\ViewProperty::route('/{record}'),
            'approve' => \App\Filament\Resources\Properties\Pages\ApproveProperty::route('/{record}/approve'),
            'reject' => \App\Filament\Resources\Properties\Pages\RejectProperty::route('/{record}/reject'),
            'archive' => \App\Filament\Resources\Properties\Pages\ArchiveProperty::route('/{record}/archive'),
        ];
    }
}