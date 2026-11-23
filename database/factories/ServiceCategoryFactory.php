<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->randomElement([
            'سباكة',
            'نجارة',
            'كهرباء',
            'دهانات',
            'تكييف وتبريد',
            'تنظيف',
            'صيانة منزلية',
            'تشطيبات وديكور',
            'نقل أثاث',
        ]);

        return [
            'name_ar' => $name,
            'name_en' => null,
            'slug' => (Str::slug($name) ?: 'service').'-'.Str::lower(Str::random(6)),
            'description' => fake('ar_EG')->sentence(),
            'icon' => 'heroicon-o-wrench-screwdriver',
            'image_path' => null,
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
