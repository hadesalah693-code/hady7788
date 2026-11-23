<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $index => $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name_ar' => $category['name_ar'],
                    'name_en' => $category['name_en'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{slug: string, name_ar: string, name_en: string, description: string, icon: string}>
     */
    private function categories(): array
    {
        return [
            [
                'slug' => 'plumbing',
                'name_ar' => 'السباكة',
                'name_en' => 'Plumbing',
                'description' => 'إصلاح وصيانة السباكة، تسريب المياه، تركيب الأدوات الصحية.',
                'icon' => 'heroicon-o-wrench-screwdriver',
            ],
            [
                'slug' => 'carpentry',
                'name_ar' => 'النجارة',
                'name_en' => 'Carpentry',
                'description' => 'أعمال النجارة، تركيب الأبواب والشبابيك والمطابخ.',
                'icon' => 'heroicon-o-cube',
            ],
            [
                'slug' => 'electricity',
                'name_ar' => 'الكهرباء',
                'name_en' => 'Electricity',
                'description' => 'إصلاح الأعطال الكهربائية، تركيب الإنارة والمفاتيح.',
                'icon' => 'heroicon-o-bolt',
            ],
            [
                'slug' => 'painting',
                'name_ar' => 'الدهانات',
                'name_en' => 'Painting',
                'description' => 'دهانات داخلية وخارجية، ورق جدران، وأعمال المعجون.',
                'icon' => 'heroicon-o-paint-brush',
            ],
            [
                'slug' => 'ac-refrigeration',
                'name_ar' => 'التكييف والتبريد',
                'name_en' => 'AC & Refrigeration',
                'description' => 'تركيب وصيانة التكييفات والثلاجات وأجهزة التبريد.',
                'icon' => 'heroicon-o-fire',
            ],
            [
                'slug' => 'cleaning',
                'name_ar' => 'التنظيف',
                'name_en' => 'Cleaning',
                'description' => 'تنظيف المنازل والشقق والمكاتب بعد التشطيب أو الانتقال.',
                'icon' => 'heroicon-o-sparkles',
            ],
            [
                'slug' => 'home-maintenance',
                'name_ar' => 'الصيانة المنزلية',
                'name_en' => 'Home Maintenance',
                'description' => 'صيانة عامة للمنزل تشمل الإصلاحات البسيطة والمتوسطة.',
                'icon' => 'heroicon-o-home-modern',
            ],
            [
                'slug' => 'finishing-decoration',
                'name_ar' => 'التشطيبات والديكور',
                'name_en' => 'Finishing & Decoration',
                'description' => 'أعمال التشطيبات الكاملة والديكور الداخلي والخارجي.',
                'icon' => 'heroicon-o-building-office-2',
            ],
            [
                'slug' => 'furniture-moving',
                'name_ar' => 'نقل الأثاث',
                'name_en' => 'Furniture Moving',
                'description' => 'نقل الأثاث والأجهزة مع الفك والتركيب والتغليف.',
                'icon' => 'heroicon-o-truck',
            ],
        ];
    }
}
