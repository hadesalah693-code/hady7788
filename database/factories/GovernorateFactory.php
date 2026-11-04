<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Governorate>
 */
class GovernorateFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->unique()->city();

        return [
            'name_ar' => $name,
            'name_en' => null,
            'slug' => (Str::slug($name) ?: 'governorate').'-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ];
    }
}
