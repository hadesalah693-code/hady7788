<?php

namespace Database\Factories;

use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PropertyType>
 */
class PropertyTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->word();

        return [
            'name_ar' => $name,
            'name_en' => null,
            'slug' => (Str::slug($name) ?: 'type').'-'.Str::lower(Str::random(6)),
            'icon' => 'apartment',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
