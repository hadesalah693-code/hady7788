<?php

namespace App\Filament\Resources\CommissionSettings;

use App\Enums\CommissionType;
use App\Filament\Resources\CommissionSettings\Pages\CreateCommissionSetting;
use App\Filament\Resources\CommissionSettings\Pages\EditCommissionSetting;
use App\Filament\Resources\CommissionSettings\Pages\ListCommissionSettings;
use App\Models\CommissionSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CommissionSettingResource extends Resource
{
    protected static ?string $model = CommissionSetting::class;

    protected static ?string $navigationLabel = 'إعدادات العمولة';

    protected static ?string $modelLabel = 'إعداد عمولة';

    protected static ?string $pluralModelLabel = 'إعدادات العمولة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الخدمات';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('commission_type')
                    ->label('طريقة حساب العمولة')
                    ->options(collect(CommissionType::cases())
                        ->mapWithKeys(fn (CommissionType $type): array => [$type->value => $type->label()])
                        ->all())
                    ->default(CommissionType::Fixed->value)
                    ->required()
                    ->live(),

                TextInput::make('fixed_amount')
                    ->label('المبلغ الثابت لكل طلب')
                    ->numeric()
                    ->prefix('ج.م ')
                    ->default(50)
                    ->visible(fn ($get): bool => $get('commission_type') === CommissionType::Fixed->value),

                TextInput::make('percentage_rate')
                    ->label('النسبة المئوية (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(5)
                    ->visible(fn ($get): bool => $get('commission_type') === CommissionType::Percentage->value),

                TextInput::make('min_amount')
                    ->label('الحد الأدنى للعمولة')
                    ->numeric()
                    ->prefix('ج.م ')
                    ->default(0),

                Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true)
                    ->helperText('عند تفعيل إعداد جديد يتم تعطيل الإعدادات السابقة تلقائيًا.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('commission_type')
                    ->label('الطريقة')
                    ->formatStateUsing(fn (CommissionType $state): string => $state->label()),

                TextColumn::make('fixed_amount')
                    ->label('المبلغ الثابت')
                    ->money('EGP'),

                TextColumn::make('percentage_rate')
                    ->label('النسبة %')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('min_amount')
                    ->label('الحد الأدنى')
                    ->money('EGP'),

                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissionSettings::route('/'),
            'create' => CreateCommissionSetting::route('/create'),
            'edit' => EditCommissionSetting::route('/{record}/edit'),
        ];
    }
}
