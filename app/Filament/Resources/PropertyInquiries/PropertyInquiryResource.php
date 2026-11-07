<?php

namespace App\Filament\Resources\PropertyInquiries;

use App\Filament\Resources\PropertyInquiries\Pages\EditPropertyInquiry;
use App\Filament\Resources\PropertyInquiries\Pages\ListPropertyInquiries;
use App\Filament\Resources\PropertyInquiries\Pages\ViewPropertyInquiry;
use App\Models\PropertyInquiry;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertyInquiryResource extends Resource
{
    protected static ?string $model = PropertyInquiry::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'استفسارات العقارات';

    protected static ?string $modelLabel = 'استفسار';

    protected static ?string $pluralModelLabel = 'استفسارات العقارات';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الطلب')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('اسم العميل')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->tel()
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('property.title')
                        ->label('العقار المطلوب')
                        ->disabled()
                        ->dehydrated(false),

                    Textarea::make('message')
                        ->label('تفاصيل الاستفسار')
                        ->rows(5)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    Select::make('status')
                        ->label('حالة المتابعة')
                        ->options([
                            PropertyInquiry::STATUS_NEW => 'جديد',
                            PropertyInquiry::STATUS_IN_PROGRESS => 'جارٍ المتابعة',
                            PropertyInquiry::STATUS_CLOSED => 'مغلق',
                        ])
                        ->required(),

                    Textarea::make('internal_notes')
                        ->label('ملاحظات داخلية للفريق')
                        ->rows(4)
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الطلب')
                ->columns(2)
                ->schema([
                    TextEntry::make('property.title')->label('العقار'),
                    TextEntry::make('name')->label('اسم العميل'),
                    TextEntry::make('phone')->label('رقم الهاتف'),
                    TextEntry::make('email')->label('البريد الإلكتروني'),
                    TextEntry::make('status')->label('الحالة')->badge(),
                    TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime(),
                    TextEntry::make('message')->label('تفاصيل الاستفسار')->columnSpanFull(),
                    TextEntry::make('internal_notes')->label('ملاحظات الفريق')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->label('العقار')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('name')
                    ->label('العميل')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('حالة المتابعة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة المتابعة')
                    ->options([
                        PropertyInquiry::STATUS_NEW => 'جديد',
                        PropertyInquiry::STATUS_IN_PROGRESS => 'جارٍ المتابعة',
                        PropertyInquiry::STATUS_CLOSED => 'مغلق',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyInquiries::route('/'),
            'view' => ViewPropertyInquiry::route('/{record}'),
            'edit' => EditPropertyInquiry::route('/{record}/edit'),
        ];
    }
}
