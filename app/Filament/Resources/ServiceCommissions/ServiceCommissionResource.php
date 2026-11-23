<?php

namespace App\Filament\Resources\ServiceCommissions;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Filament\Resources\ServiceCommissions\Pages\ListServiceCommissions;
use App\Filament\Resources\ServiceCommissions\Pages\ViewServiceCommission;
use App\Models\ServiceCommission;
use App\Services\ServiceCommissionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ServiceCommissionResource extends Resource
{
    protected static ?string $model = ServiceCommission::class;

    protected static ?string $navigationLabel = 'العمولات';

    protected static ?string $modelLabel = 'عمولة';

    protected static ?string $pluralModelLabel = 'العمولات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الخدمات';

    protected static ?int $navigationSort = 5;

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return collect(CommissionStatus::cases())
            ->mapWithKeys(fn (CommissionStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(CommissionType::cases())
            ->mapWithKeys(fn (CommissionType $type): array => [$type->value => $type->label()])
            ->all();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل العمولة')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('request.reference')->label('رقم الطلب'),
                        TextEntry::make('provider.display_name')->label('مقدم الخدمة')->placeholder('-'),
                        TextEntry::make('type')
                            ->label('نوع العمولة')
                            ->formatStateUsing(fn (CommissionType $state): string => $state->label()),
                        TextEntry::make('rate')->label('القيمة/النسبة')->numeric(decimalPlaces: 2),
                        TextEntry::make('amount')->label('قيمة العمولة')->money('EGP'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (CommissionStatus $state): string => $state->label())
                            ->color(fn (CommissionStatus $state): string => $state->color()),
                        TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
                        TextEntry::make('settled_at')->label('تاريخ التحصيل')->dateTime()->placeholder('-'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('request.reference')
                    ->label('رقم الطلب')
                    ->searchable(),

                TextColumn::make('provider.display_name')
                    ->label('مقدم الخدمة')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (CommissionType $state): string => $state->label())
                    ->toggleable(),

                TextColumn::make('rate')
                    ->label('القيمة/النسبة')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('amount')
                    ->label('العمولة')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (CommissionStatus $state): string => $state->label())
                    ->color(fn (CommissionStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('settled_at')
                    ->label('تاريخ التحصيل')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(self::statusOptions()),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(self::typeOptions()),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('settle')
                    ->label('تحصيل')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceCommission $record): bool => $record->status === CommissionStatus::Pending)
                    ->action(function (ServiceCommission $record): void {
                        app(ServiceCommissionService::class)->settle($record);

                        Notification::make()->title('تم تسجيل تحصيل العمولة.')->success()->send();
                    }),

                Action::make('waive')
                    ->label('إعفاء')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceCommission $record): bool => $record->status === CommissionStatus::Pending)
                    ->action(function (ServiceCommission $record): void {
                        app(ServiceCommissionService::class)->waive($record);

                        Notification::make()->title('تم إعفاء العمولة.')->success()->send();
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceCommissions::route('/'),
            'view' => ViewServiceCommission::route('/{record}'),
        ];
    }
}
