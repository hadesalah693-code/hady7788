<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\ServiceProviderStatus;
use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = Governorate::query()->where('is_active', true)->orderBy('id')->get();

        if ($governorates->isEmpty()) {
            $this->command?->warn('تم تخطي مقدّمي الخدمات التجريبيين: لا توجد محافظات.');

            return;
        }

        foreach ($this->providers() as $index => $data) {
            $governorate = $governorates[$index % $governorates->count()];

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Str::password(20),
                    'account_type' => AccountType::User,
                    'status' => AccountStatus::Active,
                    'email_verified_at' => now(),
                ],
            );

            $provider = ServiceProvider::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $data['name'],
                    'phone' => $data['phone'],
                    'bio' => $data['bio'],
                    'experience_years' => $data['experience'],
                    'governorate_id' => $governorate->id,
                    'city_id' => null,
                    'service_areas' => $data['areas'],
                    'status' => ServiceProviderStatus::Active,
                    'is_available' => true,
                    'approved_at' => now()->subDays($index + 2),
                ],
            );

            $provider->forceFill([
                'rating_avg' => $data['rating'],
                'reviews_count' => $data['reviews'],
                'completed_requests_count' => $data['completed'],
            ])->save();

            $categoryIds = ServiceCategory::query()
                ->whereIn('slug', $data['categories'])
                ->pluck('id')
                ->all();

            if ($categoryIds !== []) {
                $provider->categories()->sync($categoryIds);
            }
        }
    }

    /**
     * @return array<int, array{
     *     name: string, email: string, phone: string, bio: string,
     *     experience: int, areas: array<int, string>, rating: float,
     *     reviews: int, completed: int, categories: array<int, string>
     * }>
     */
    private function providers(): array
    {
        return [
            [
                'name' => 'مؤسسة النخبة للسباكة',
                'email' => 'provider.plumbing@emdad.test',
                'phone' => '01001234567',
                'bio' => 'فريق متخصص في إصلاح وصيانة السباكة المنزلية، كشف التسريبات وتركيب الأدوات الصحية بأحدث المعدات وضمان على التنفيذ.',
                'experience' => 12,
                'areas' => ['مدينة نصر', 'مصر الجديدة', 'المعادي'],
                'rating' => 4.9,
                'reviews' => 128,
                'completed' => 340,
                'categories' => ['plumbing', 'home-maintenance'],
            ],
            [
                'name' => 'ورشة الإتقان للنجارة',
                'email' => 'provider.carpentry@emdad.test',
                'phone' => '01002345678',
                'bio' => 'تصميم وتنفيذ أعمال النجارة الحديثة، تركيب الأبواب والمطابخ والخزائن بخامات عالية الجودة وتسليم في الموعد.',
                'experience' => 15,
                'areas' => ['الهرم', 'فيصل', 'الدقي'],
                'rating' => 4.8,
                'reviews' => 96,
                'completed' => 210,
                'categories' => ['carpentry', 'finishing-decoration'],
            ],
            [
                'name' => 'فريق الطاقة للكهرباء',
                'email' => 'provider.electricity@emdad.test',
                'phone' => '01003456789',
                'bio' => 'إصلاح الأعطال الكهربائية وتركيب الإنارة والمفاتيح ولوحات التوزيع، مع فحص أمن وسلامة التمديدات.',
                'experience' => 9,
                'areas' => ['سموحة', 'سيدي جابر', 'العجمي'],
                'rating' => 4.7,
                'reviews' => 74,
                'completed' => 165,
                'categories' => ['electricity', 'home-maintenance'],
            ],
            [
                'name' => 'لمسة ألوان للدهانات',
                'email' => 'provider.painting@emdad.test',
                'phone' => '01004567890',
                'bio' => 'دهانات داخلية وخارجية، ورق جدران، وأعمال معجون وتشطيبات نهائية بمظهر عصري ونظافة تامة بعد التنفيذ.',
                'experience' => 11,
                'areas' => ['المنصورة', 'طلخا', 'ميت غمر'],
                'rating' => 4.9,
                'reviews' => 143,
                'completed' => 280,
                'categories' => ['painting', 'finishing-decoration'],
            ],
            [
                'name' => 'مركز التبريد الحديث',
                'email' => 'provider.ac@emdad.test',
                'phone' => '01005678901',
                'bio' => 'تركيب وصيانة التكييفات والثلاجات وأجهزة التبريد، شحن فريون وتنظيف دوري بعقود صيانة مرنة.',
                'experience' => 8,
                'areas' => ['المنتزه', 'سيدي بشر', 'الشاطبي'],
                'rating' => 4.6,
                'reviews' => 61,
                'completed' => 150,
                'categories' => ['ac-refrigeration', 'home-maintenance'],
            ],
            [
                'name' => 'شركة البيت النظيف',
                'email' => 'provider.cleaning@emdad.test',
                'phone' => '01006789012',
                'bio' => 'تنظيف المنازل والشقق والمكاتب بعد التشطيب أو الانتقال، بأدوات آمنة وفريق مدرب واحترام كامل للخصوصية.',
                'experience' => 6,
                'areas' => ['الرحاب', 'مدينتي', 'الشروق'],
                'rating' => 4.8,
                'reviews' => 189,
                'completed' => 420,
                'categories' => ['cleaning'],
            ],
            [
                'name' => 'مؤسسة التشطيب المتكامل',
                'email' => 'provider.finishing@emdad.test',
                'phone' => '01007890123',
                'bio' => 'تشطيبات كاملة من التصميم للتنفيذ: أسقف جبس، بلاط، دهانات، ديكور داخلي، وإدارة كاملة للمشروع.',
                'experience' => 18,
                'areas' => ['التجمع الخامس', 'القطامية', 'المعادي'],
                'rating' => 4.9,
                'reviews' => 87,
                'completed' => 130,
                'categories' => ['finishing-decoration', 'painting', 'carpentry'],
            ],
            [
                'name' => 'نقل الأثاث الآمن',
                'email' => 'provider.moving@emdad.test',
                'phone' => '01008901234',
                'bio' => 'نقل الأثاث والأجهزة مع الفك والتركيب والتغليف الاحترافي، سيارات مغلقة وفريق تحميل مدرب.',
                'experience' => 7,
                'areas' => ['6 أكتوبر', 'الشيخ زايد', 'حدائق أكتوبر'],
                'rating' => 4.7,
                'reviews' => 112,
                'completed' => 260,
                'categories' => ['furniture-moving', 'home-maintenance'],
            ],
        ];
    }
}
