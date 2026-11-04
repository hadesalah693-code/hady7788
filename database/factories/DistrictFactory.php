<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->streetName();

        return [
            'city_id' => City::factory(),
            'name_ar' => $name,
            'name_en' => null,
            'slug' => (Str::slug($name) ?: 'district').'-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ];
    }
}
