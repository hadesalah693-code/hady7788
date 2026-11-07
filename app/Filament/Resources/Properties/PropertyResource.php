<?php

namespace App\Filament\Resources\Properties;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'العقارات';

    protected static ?string $modelLabel = 'عقار';

    protected static ?string $pluralModelLabel = 'العقارات';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العقار')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('عنوان العقار')
                        ->required()
                        ->minLength(3)
                        ->maxLength(200),

                    Select::make('type')
                        ->label('نوع العقار')
                        ->relationship('type', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('purpose')
                        ->label('الغرض')
                        ->options([
                            'sale' => 'للبيع',
                            'rent' => 'للإيجار',
                        ])
                        ->required(),

                    TextInput::make('price')
                        ->label('السعر')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    Select::make('currency')
                        ->label('العملة')
                        ->options([
                            'EGP' => 'جنيه مصري',
                            'USD' => 'دولار أمريكي',
                            'EUR' => 'يورو',
                        ])
                        ->default('EGP')
                        ->required(),

                    TextInput::make('area')
                        ->label('المساحة (م²)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),

                    Select::make('governorate_id')
                        ->label('المحافظة')
                        ->relationship('governorate', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('city_id')
                        ->label('المدينة')
                        ->relationship('city', 'name_ar')
                        ->searchable()
                        ->preload(),

                    Select::make('district_id')
                        ->label('المنطقة')
                        ->relationship('district', 'name_ar')
                        ->searchable()
                        ->preload(),

                    TextInput::make('bedrooms')
                        ->label('عدد الغرف')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(50),

                    TextInput::make('bathrooms')
                        ->label('عدد الحمامات')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(50),

                    TextInput::make('floor')
                        ->label('الطابق')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('total_floors')
                        ->label('إجمالي الطوابق')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('parking_spaces')
                        ->label('أماكن الانتظار')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('year_built')
                        ->label('عام البناء')
                        ->numeric()
                        ->minValue(1800)
                        ->maxValue((int) date('Y') + 2),

                    Select::make('finishing')
                        ->label('التشطيب')
                        ->options([
                            'unfinished' => 'غير مشطب',
                            'semi_finished' => 'نصف مشطب',
                            'finished' => 'مشطب',
                            'furnished' => 'مفروش',
                        ]),

                    TextInput::make('available_from')
                        ->label('متاح من')
                        ->type('date'),

                    TextInput::make('contact_name')
                        ->label('اسم جهة التواصل')
                        ->maxLength(120),

                    TextInput::make('contact_phone')
                        ->label('هاتف التواصل')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('contact_whatsapp')
                        ->label('واتساب التواصل')
                        ->tel()
                        ->maxLength(20),

                    Textarea::make('description')
                        ->label('وصف العقار')
                        ->required()
                        ->maxLength(5000)
                        ->rows(5)
                        ->columnSpanFull(),

                    Textarea::make('address')
                        ->label('العنوان التفصيلي (داخلي)')
                        ->helperText('لا يظهر العنوان التفصيلي للزوار.')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Toggle::make('is_featured')
                        ->label('عقار مميز')
                        ->default(false),

                    Select::make('status')
                        ->label('الحالة')
                        ->options(collect(PropertyStatus::cases())
                            ->mapWithKeys(fn (PropertyStatus $status): array => [$status->value => $status->label()])
                            ->all())
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العقار')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->label('العنوان'),
                    TextEntry::make('slug')->label('الرابط'),
                    TextEntry::make('user.name')->label('صاحب الإعلان'),
                    TextEntry::make('type.name_ar')->label('نوع العقار'),
                    TextEntry::make('purpose')->label('الغرض'),
                    TextEntry::make('status')->label('الحالة')->badge(),
                    TextEntry::make('price')
                        ->label('السعر')
                        ->formatStateUsing(fn ($state, Property $record): string =>
                            number_format((float) $state, 2).' '.$record->currency_label),
                    TextEntry::make('area')->label('المساحة (م²)'),
                    TextEntry::make('governorate.name_ar')->label('المحافظة'),
                    TextEntry::make('city.name_ar')->label('المدينة'),
                    TextEntry::make('district.name_ar')->label('المنطقة'),
                    TextEntry::make('address')->label('العنوان التفصيلي (للمدير فقط)'),
                    TextEntry::make('contact_phone')->label('هاتف التواصل'),
                    TextEntry::make('contact_whatsapp')->label('واتساب التواصل'),
                    TextEntry::make('rejection_reason')->label('سبب الرفض'),
                    TextEntry::make('archive_reason')->label('سبب الأرشفة'),
                    TextEntry::make('created_at')->label('تاريخ الإضافة')->dateTime(),
                    TextEntry::make('published_at')->label('تاريخ النشر')->dateTime(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('العقار')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('user.name')
                    ->label('المعلن')
                    ->searchable(),

                TextColumn::make('type.name_ar')
                    ->label('النوع')
                    ->sortable(),

                TextColumn::make('purpose')
                    ->label('الغرض')
                    ->badge(),

                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state, Property $record): string =>
                        number_format((float) $state, 0).' '.$record->currency_label)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PropertyStatus::cases())
                        ->mapWithKeys(fn (PropertyStatus $status): array => [$status->value => $status->label()])
                        ->all()),

                SelectFilter::make('purpose')
                    ->label('الغرض')
                    ->options([
                        'sale' => 'للبيع',
                        'rent' => 'للإيجار',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('approve')
                    ->label('اعتماد ونشر')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Property $record): bool => $record->status === PropertyStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function (Property $record): void {
                        Gate::authorize('publish', $record);

                        $record->forceFill([
                            'status' => PropertyStatus::Published,
                            'published_at' => now(),
                            'published_by' => auth()->id(),
                            'rejection_reason' => null,
                            'archive_reason' => null,
                        ])->save();
                    }),

                Action::make('reject')
                    ->label('رفض الإعلان')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Property $record): bool => $record->status === PropertyStatus::Pending)
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Property $record, array $data): void {
                        Gate::authorize('update', $record);

                        $record->forceFill([
                            'status' => PropertyStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                            'published_at' => null,
                        ])->save();
                    }),

                Action::make('archive')
                    ->label('أرشفة')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('gray')
                    ->visible(fn (Property $record): bool => $record->status !== PropertyStatus::Archived)
                    ->schema([
                        Textarea::make('archive_reason')
                            ->label('سبب الأرشفة')
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Property $record, array $data): void {
                        Gate::authorize('update', $record);

                        $record->forceFill([
                            'status' => PropertyStatus::Archived,
                            'archive_reason' => $data['archive_reason'] ?? null,
                        ])->save();
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
