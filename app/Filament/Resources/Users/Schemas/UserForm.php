<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(120),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('whatsapp')
                    ->label('واتساب')
                    ->tel()
                    ->maxLength(20),

                Textarea::make('bio')
                    ->label('نبذة')
                    ->rows(3)
                    ->columnSpanFull(),

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

                Select::make('account_type')
                    ->label('نوع الحساب')
                    ->options(AccountType::class)
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('حالة الحساب')
                    ->options(AccountStatus::class)
                    ->disabled()
                    ->dehydrated(false),

                Toggle::make('is_admin')
                    ->label('مدير')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
