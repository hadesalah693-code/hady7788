<?php

namespace App\Filament\Resources\ServiceProviders;

use App\Enums\ServiceProviderStatus;
use App\Filament\Resources\ServiceProviders\Pages\CreateServiceProvider;
use App\Filament\Resources\ServiceProviders\Pages\EditServiceProvider;
use App\Filament\Resources\ServiceProviders\Pages\ListServiceProviders;
use App\Filament\Resources\ServiceProviders\Pages\ViewServiceProvider;
use App\Models\ServiceProvider;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ServiceProviderResource extends Resource
{
    protected static ?string $model = ServiceProvider::class;

    protected static ?string $navigationLabel = 'مقدمو الخدمات';

    protected static ?string $modelLabel = 'مقدم خدمة';

    protected static ?string $pluralModelLabel = 'مقدمو الخدمات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الخدمات';

    protected static ?int $navigationSort = 2;

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return collect(ServiceProviderStatus::cases())
            ->mapWithKeys(fn (ServiceProviderStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات مقدم الخدمة')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('حساب المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('display_name')
                            ->label('اسم مقدم الخدمة')
                            ->required()
                            ->maxLength(120),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        TextInput::make('experience_years')
                            ->label('سنوات الخبرة')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->default(0),

                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name_ar')
                            ->searchable()
                            ->preload(),

                        Select::make('city_id')
                            ->label('المدينة')
                            ->relationship('city', 'name_ar')
                            ->searchable()
                            ->preload(),

                        Textarea::make('bio')
                            ->label('نبذة')
                            ->rows(4)
                            ->columnSpanFull(),

                        TagsInput::make('service_areas')
                            ->label('مناطق العمل')
                            ->placeholder('أضف منطقة ثم اضغط Enter')
                            ->columnSpanFull(),

                        Select::make('categories')
                            ->label('التخصصات')
                            ->relationship('categories', 'name_ar')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('حالة الحساب')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->options(self::statusOptions())
                            ->default(ServiceProviderStatus::Pending->value)
                            ->required(),

                        Toggle::make('is_available')
                            ->label('متاح لاستقبال الطلبات')
                            ->default(true),

                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات مقدم الخدمة')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('display_name')->label('الاسم'),
                        TextEntry::make('user.name')->label('حساب المستخدم'),
                        TextEntry::make('phone')->label('الهاتف')->placeholder('-'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (ServiceProviderStatus $state): string => $state->label())
                            ->color(fn (ServiceProviderStatus $state): string => $state->color()),
                        TextEntry::make('governorate.name_ar')->label('المحافظة')->placeholder('-'),
                        TextEntry::make('city.name_ar')->label('المدينة')->placeholder('-'),
                        TextEntry::make('experience_years')->label('سنوات الخبرة'),
                        TextEntry::make('is_available')
                            ->label('متاح')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'نعم' : 'لا'),
                        TextEntry::make('rating_avg')->label('التقييم')->numeric(decimalPlaces: 2),
                        TextEntry::make('reviews_count')->label('عدد التقييمات'),
                        TextEntry::make('completed_requests_count')->label('طلبات مكتملة'),
                        TextEntry::make('approved_at')->label('تاريخ الاعتماد')->dateTime()->placeholder('-'),
                        TextEntry::make('bio')->label('نبذة')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('service_areas')
                            ->label('مناطق العمل')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode('، ', $state) : (string) ($state ?? '-'))
                            ->columnSpanFull(),
                        TextEntry::make('categories.name_ar')
                            ->label('التخصصات')
                            ->badge()
                            ->columnSpanFull(),
                        TextEntry::make('rejection_reason')->label('سبب الرفض')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('الحساب')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('governorate.name_ar')
                    ->label('المحافظة')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (ServiceProviderStatus $state): string => $state->label())
                    ->color(fn (ServiceProviderStatus $state): string => $state->color())
                    ->sortable(),

                IconColumn::make('is_available')
                    ->label('متاح')
                    ->boolean(),

                TextColumn::make('rating_avg')
                    ->label('التقييم')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('completed_requests_count')
                    ->label('مكتملة')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(self::statusOptions()),

                SelectFilter::make('governorate_id')
                    ->label('المحافظة')
                    ->relationship('governorate', 'name_ar')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_available')
                    ->label('متاح'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceProvider $record): bool => $record->status !== ServiceProviderStatus::Active)
                    ->action(function (ServiceProvider $record): void {
                        $record->forceFill([
                            'status' => ServiceProviderStatus::Active,
                            'is_available' => true,
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'rejection_reason' => null,
                        ])->save();

                        Notification::make()->title('تم اعتماد مقدم الخدمة')->success()->send();
                    }),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ServiceProvider $record): bool => $record->status !== ServiceProviderStatus::Rejected)
                    ->schema([
                        Textarea::make('reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ServiceProvider $record, array $data): void {
                        $record->forceFill([
                            'status' => ServiceProviderStatus::Rejected,
                            'is_available' => false,
                            'approved_at' => null,
                            'approved_by' => auth()->id(),
                            'rejection_reason' => $data['reason'],
                        ])->save();

                        Notification::make()->title('تم رفض مقدم الخدمة')->danger()->send();
                    }),

                Action::make('suspend')
                    ->label('إيقاف')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceProvider $record): bool => $record->status === ServiceProviderStatus::Active)
                    ->action(function (ServiceProvider $record): void {
                        $record->forceFill([
                            'status' => ServiceProviderStatus::Suspended,
                            'is_available' => false,
                        ])->save();

                        Notification::make()->title('تم إيقاف مقدم الخدمة')->warning()->send();
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceProviders::route('/'),
            'create' => CreateServiceProvider::route('/create'),
            'view' => ViewServiceProvider::route('/{record}'),
            'edit' => EditServiceProvider::route('/{record}/edit'),
        ];
    }
}
