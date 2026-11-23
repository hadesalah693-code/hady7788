<?php

namespace App\Filament\Resources\ServiceReviews;

use App\Filament\Resources\ServiceReviews\Pages\ListServiceReviews;
use App\Filament\Resources\ServiceReviews\Pages\ViewServiceReview;
use App\Models\ServiceReview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
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

class ServiceReviewResource extends Resource
{
    protected static ?string $model = ServiceReview::class;

    protected static ?string $navigationLabel = 'التقييمات والبلاغات';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الخدمات';

    protected static ?int $navigationSort = 4;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات التقييم')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('request.reference')->label('رقم الطلب'),
                        TextEntry::make('rating')->label('التقييم')->badge()->color('warning'),
                        TextEntry::make('provider.display_name')->label('مقدم الخدمة')->placeholder('-'),
                        TextEntry::make('customer.name')->label('العميل')->placeholder('-'),
                        TextEntry::make('comment')->label('التعليق')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('created_at')->label('تاريخ التقييم')->dateTime(),
                        TextEntry::make('is_reported')
                            ->label('مُبلّغ عنه')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'نعم' : 'لا'),
                        TextEntry::make('report_reason')->label('سبب البلاغ')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('reported_at')->label('تاريخ البلاغ')->dateTime()->placeholder('-'),
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

                TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(50)
                    ->placeholder('-'),

                IconColumn::make('is_reported')
                    ->label('بلاغ')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([1 => '1 نجمة', 2 => 'نجمتان', 3 => '3 نجوم', 4 => '4 نجوم', 5 => '5 نجوم']),

                TernaryFilter::make('is_reported')
                    ->label('مُبلّغ عنه'),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('dismissReport')
                    ->label('إغلاق البلاغ')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceReview $record): bool => $record->is_reported)
                    ->action(function (ServiceReview $record): void {
                        $record->forceFill([
                            'is_reported' => false,
                            'report_reason' => null,
                            'reported_at' => null,
                        ])->save();

                        Notification::make()->title('تم إغلاق البلاغ.')->success()->send();
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceReviews::route('/'),
            'view' => ViewServiceReview::route('/{record}'),
        ];
    }
}
