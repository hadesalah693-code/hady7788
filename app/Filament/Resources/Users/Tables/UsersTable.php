<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Services\AccountSuspensionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('account_type')
                    ->label('نوع الحساب')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('verification_status')
                    ->label('التوثيق')
                    ->getStateUsing(fn (User $record): string => $record->profileVerificationStatus()?->label() ?? '—')
                    ->badge()
                    ->color(fn (?User $record): string => $record?->profileVerificationStatus()?->color() ?? 'gray')
                    ->visible(fn (?User $record): bool => $record?->isBroker() || $record?->isCompany()),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('account_type')
                    ->label('نوع الحساب')
                    ->options(AccountType::class),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(AccountStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                Action::make('verify')
                    ->label('اعتماد التوثيق')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (?User $record): bool => ($record?->isBroker() || $record?->isCompany())
                        && $record?->profileVerificationStatus() !== VerificationStatus::Verified)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $profile = $record->isBroker()
                            ? $record->brokerProfile
                            : $record->companyProfile;

                        if (! $profile) {
                            return;
                        }

                        $profile->forceFill([
                            'verification_status' => VerificationStatus::Verified,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ])->save();

                        if ($record->status === AccountStatus::Pending) {
                            $record->forceFill(['status' => AccountStatus::Active])->save();
                        }
                    }),

                Action::make('rejectVerification')
                    ->label('رفض التوثيق')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (?User $record): bool => ($record?->isBroker() || $record?->isCompany())
                        && $record?->profileVerificationStatus() !== VerificationStatus::Rejected)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $profile = $record->isBroker()
                            ? $record->brokerProfile
                            : $record->companyProfile;

                        if (! $profile) {
                            return;
                        }

                        $profile->forceFill([
                            'verification_status' => VerificationStatus::Rejected,
                            'verified_at' => null,
                            'verified_by' => null,
                        ])->save();
                    }),

                Action::make('suspend')
                    ->label('إيقاف الحساب')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (?User $record): bool => $record?->getKey() !== auth()->id()
                        && $record?->status !== AccountStatus::Suspended)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        app(AccountSuspensionService::class)->suspend($record);
                    }),

                Action::make('reactivate')
                    ->label('إعادة تفعيل')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (?User $record): bool => $record?->getKey() !== auth()->id()
                        && $record?->status === AccountStatus::Suspended)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        app(AccountSuspensionService::class)->reactivate($record);
                    }),
            ])
            ->toolbarActions([]);
    }
}
