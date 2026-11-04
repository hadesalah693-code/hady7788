<?php

namespace Database\Seeders;

use App\Enums\AmenityCategory;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->amenities() as $index => $amenity) {
            Amenity::updateOrCreate(
                ['slug' => $amenity['slug']],
                [
                    'name_ar' => $amenity['name_ar'],
                    'name_en' => $amenity['name_en'],
                    'category' => $amenity['category'],
                    'sort_order' => $index,
                    'icon' => $amenity['icon'],
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{slug: string, name_ar: string, name_en: string, category: AmenityCategory, icon: string}>
     */
    private function amenities(): array
    {
        return [
            // Inside the property.
            ['slug' => 'air-conditioning', 'name_ar' => 'تكييف', 'name_en' => 'Air conditioning', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-sun'],
            ['slug' => 'equipped-kitchen', 'name_ar' => 'مطبخ مجهز', 'name_en' => 'Equipped kitchen', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-cake'],
            ['slug' => 'balcony', 'name_ar' => 'شرفة', 'name_en' => 'Balcony', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-window'],
            ['slug' => 'heating', 'name_ar' => 'تدفئة', 'name_en' => 'Heating', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-fire'],
            ['slug' => 'luxury-finishing', 'name_ar' => 'تشطيب فاخر', 'name_en' => 'Luxury finishing', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-sparkles'],
            ['slug' => 'home-appliances', 'name_ar' => 'أجهزة كهربائية', 'name_en' => 'Home appliances', 'category' => AmenityCategory::Indoor, 'icon' => 'heroicon-o-tv'],

            // Building facilities.
            ['slug' => 'elevator', 'name_ar' => 'أسانسير', 'name_en' => 'Elevator', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-arrows-up-down'],
            ['slug' => 'garage', 'name_ar' => 'جراج', 'name_en' => 'Garage', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-truck'],
            ['slug' => 'security', 'name_ar' => 'أمن وحراسة', 'name_en' => 'Security', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-shield-check'],
            ['slug' => 'cctv', 'name_ar' => 'كاميرات مراقبة', 'name_en' => 'CCTV', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-video-camera'],
            ['slug' => 'swimming-pool', 'name_ar' => 'حمام سباحة', 'name_en' => 'Swimming pool', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-cloud'],
            ['slug' => 'gym', 'name_ar' => 'جيم', 'name_en' => 'Gym', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-bolt'],
            ['slug' => 'garden', 'name_ar' => 'حديقة', 'name_en' => 'Garden', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-sun'],
            ['slug' => 'electronic-gate', 'name_ar' => 'بوابة إلكترونية', 'name_en' => 'Electronic gate', 'category' => AmenityCategory::Building, 'icon' => 'heroicon-o-lock-closed'],

            // Extra services.
            ['slug' => 'internet', 'name_ar' => 'إنترنت', 'name_en' => 'Internet', 'category' => AmenityCategory::Extra, 'icon' => 'heroicon-o-wifi'],
            ['slug' => 'generator', 'name_ar' => 'مولد كهرباء', 'name_en' => 'Generator', 'category' => AmenityCategory::Extra, 'icon' => 'heroicon-o-power'],
            ['slug' => 'maintenance-services', 'name_ar' => 'خدمات صيانة', 'name_en' => 'Maintenance services', 'category' => AmenityCategory::Extra, 'icon' => 'heroicon-o-wrench-screwdriver'],
            ['slug' => 'private-entrance', 'name_ar' => 'مدخل خاص', 'name_en' => 'Private entrance', 'category' => AmenityCategory::Extra, 'icon' => 'heroicon-o-key'],
        ];
    }
}
