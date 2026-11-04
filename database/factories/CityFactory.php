<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->city();

        return [
            'governorate_id' => Governorate::factory(),
            'name_ar' => $name,
            'name_en' => null,
            'slug' => (Str::slug($name) ?: 'city').'-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ];
    }
}
