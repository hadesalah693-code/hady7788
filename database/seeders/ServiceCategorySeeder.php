<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'السباكة', 'slug' => 'plumbing', 'icon' => 'wrench'],
            ['name_ar' => 'الكهرباء', 'slug' => 'electricity', 'icon' => 'zap'],
            ['name_ar' => 'النجارة', 'slug' => 'carpentry', 'icon' => 'hammer'],
            ['name_ar' => 'التكييف والتبريد', 'slug' => 'air-conditioning', 'icon' => 'wind'],
            ['name_ar' => 'النقاشة والدهانات', 'slug' => 'painting', 'icon' => 'paintbrush'],
            ['name_ar' => 'السيراميك والأرضيات', 'slug' => 'tiles', 'icon' => 'grid'],
            ['name_ar' => 'تنظيف المنازل', 'slug' => 'cleaning', 'icon' => 'sparkles'],
            ['name_ar' => 'الألوميتال والزجاج', 'slug' => 'aluminium-glass', 'icon' => 'panels-top-left'],
        ];

        foreach ($categories as $index => $category) {
            ServiceCategory::updateOrCreate(['slug' => $category['slug']], [...$category, 'is_active' => true, 'sort_order' => $index + 1]);
        }
    }
}
