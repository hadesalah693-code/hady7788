<?php

namespace Database\Factories;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $title = fake('ar_EG')->randomElement([
            'شقة فاخرة بثلاث غرف',
            'فيلا مستقلة بحديقة خاصة',
            'شقة حديثة قريبة من الخدمات',
            'تاون هاوس بتشطيب كامل',
            'مكتب إداري في موقع حيوي',
        ]);

        $purpose = fake()->randomElement(PropertyPurpose::cases());

        return [
            'user_id' => null,
            'governorate_id' => Governorate::factory(),
            'city_id' => null,
            'district_id' => null,
            'property_type_id' => PropertyType::factory(),
            'purpose' => $purpose,
            'status' => PropertyStatus::Published,
            'title' => $title,
            'slug' => (Str::slug($title) ?: 'property').'-'.Str::lower(Str::random(8)),
            'description' => fake('ar_EG')->paragraph(5),
            'price' => fake()->numberBetween(800_000, 12_000_000),
            'area' => fake()->numberBetween(60, 450),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 4),
            'floor' => fake()->numberBetween(0, 12),
            'address' => fake('ar_EG')->address(),
            'is_featured' => fake()->boolean(40),
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'views_count' => fake()->numberBetween(20, 1500),
        ];
    }

    public function forSale(): static
    {
        return $this->state(fn () => ['purpose' => PropertyPurpose::Sale]);
    }

    public function forRent(): static
    {
        return $this->state(fn () => ['purpose' => PropertyPurpose::Rent]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
