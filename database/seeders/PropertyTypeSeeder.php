<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name_ar' => 'شقة', 'name_en' => 'Apartment', 'slug' => 'apartment', 'icon' => 'apartment'],
            ['name_ar' => 'فيلا', 'name_en' => 'Villa', 'slug' => 'villa', 'icon' => 'villa'],
            ['name_ar' => 'تاون هاوس', 'name_en' => 'Townhouse', 'slug' => 'townhouse', 'icon' => 'townhouse'],
            ['name_ar' => 'أرض', 'name_en' => 'Land', 'slug' => 'land', 'icon' => 'land'],
            ['name_ar' => 'محل تجاري', 'name_en' => 'Shop', 'slug' => 'shop', 'icon' => 'shop'],
            ['name_ar' => 'مكتب', 'name_en' => 'Office', 'slug' => 'office', 'icon' => 'office'],
            ['name_ar' => 'شاليه', 'name_en' => 'Chalet', 'slug' => 'chalet', 'icon' => 'chalet'],
            ['name_ar' => 'عمارة', 'name_en' => 'Building', 'slug' => 'building', 'icon' => 'building'],
        ];

        foreach ($types as $index => $type) {
            PropertyType::updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name_ar' => $type['name_ar'],
                    'name_en' => $type['name_en'],
                    'icon' => $type['icon'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
