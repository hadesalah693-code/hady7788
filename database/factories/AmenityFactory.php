<?php

namespace Database\Factories;

use App\Enums\AmenityCategory;
use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Amenity>
 */
class AmenityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->word();

        return [
            'name_ar' => $name,
            'name_en' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'category' => fake()->randomElement(AmenityCategory::cases()),
            'sort_order' => fake()->numberBetween(0, 20),
            'icon' => 'heroicon-o-sparkles',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
