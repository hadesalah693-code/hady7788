<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('تم تخطي إنشاء المدير: أضف ADMIN_EMAIL وADMIN_PASSWORD إلى ملف .env.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'مدير الإمداد العقارية',
                'password' => $password,
                'is_admin' => true,
                'account_type' => AccountType::User,
                'status' => AccountStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info('تم تجهيز حساب المدير: '.$email);
    }
}
