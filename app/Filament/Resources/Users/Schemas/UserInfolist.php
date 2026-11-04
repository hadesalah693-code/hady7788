<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الحساب')
                    ->schema([
                        TextEntry::make('name')->label('الاسم'),
                        TextEntry::make('email')->label('البريد الإلكتروني'),
                        TextEntry::make('phone')->label('الهاتف')->placeholder('-'),
                        TextEntry::make('whatsapp')->label('واتساب')->placeholder('-'),
                        TextEntry::make('account_type')->label('نوع الحساب')->badge(),
                        TextEntry::make('status')->label('الحالة')->badge(),
                        TextEntry::make('is_admin')->label('مدير')->formatStateUsing(fn (bool $state): string => $state ? 'نعم' : 'لا'),
                        TextEntry::make('created_at')->label('تاريخ التسجيل')->dateTime()->placeholder('-'),
                        TextEntry::make('last_login_at')->label('آخر دخول')->dateTime()->placeholder('-'),
                        TextEntry::make('bio')->label('نبذة')->placeholder('-')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('بيانات الوسيط (إن وجدت)')
                    ->schema([
                        TextEntry::make('brokerProfile.professional_title')->label('المسمى المهني')->placeholder('-'),
                        TextEntry::make('brokerProfile.license_number')->label('رقم الترخيص')->placeholder('-'),
                        TextEntry::make('brokerProfile.years_of_experience')->label('سنوات الخبرة')->placeholder('-'),
                        TextEntry::make('brokerProfile.verification_status')->label('حالة التوثيق')->badge()->placeholder('-'),
                        TextEntry::make('brokerProfile.service_areas')
                            ->label('مناطق العمل')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode('، ', $state) : (string) ($state ?? '-'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('brokerProfile.bio')->label('نبذة')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('brokerProfile.verified_at')->label('تاريخ التوثيق')->dateTime()->placeholder('-'),
                        TextEntry::make('brokerProfile.company.company_name')->label('الشركة التابعة')->placeholder('وسيط مستقل'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record): bool => $record?->isBroker() ?? false),

                Section::make('بيانات الشركة (إن وجدت)')
                    ->schema([
                        TextEntry::make('companyProfile.company_name')->label('اسم الشركة')->placeholder('-'),
                        TextEntry::make('companyProfile.commercial_register')->label('السجل التجاري')->placeholder('-'),
                        TextEntry::make('companyProfile.tax_id')->label('الرقم الضريبي')->placeholder('-'),
                        TextEntry::make('companyProfile.website')->label('الموقع الإلكتروني')->placeholder('-'),
                        TextEntry::make('companyProfile.address')->label('العنوان')->placeholder('-'),
                        TextEntry::make('companyProfile.verification_status')->label('حالة التوثيق')->badge()->placeholder('-'),
                        TextEntry::make('companyProfile.description')->label('نبذة')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('companyProfile.verified_at')->label('تاريخ التوثيق')->dateTime()->placeholder('-'),
                        TextEntry::make('companyProfile.logo_path')->label('مسار الشعار')->placeholder('-'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record): bool => $record?->isCompany() ?? false),
            ]);
    }
}
