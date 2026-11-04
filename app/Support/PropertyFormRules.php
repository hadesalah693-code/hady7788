<?php

namespace App\Support;

use App\Enums\PropertyPurpose;
use Illuminate\Validation\Rule;

/**
 * Central definition of the fields a property owner is allowed to edit.
 *
 * Administrative columns (user_id, status, is_featured, published_at,
 * published_by, views_count) are intentionally absent: they must only ever
 * be written by backend workflows.
 */
final class PropertyFormRules
{
    public const CURRENCIES = ['EGP', 'USD', 'EUR'];

    public const FINISHINGS = [
        'unfinished' => 'غير مشطب',
        'semi_finished' => 'نصف مشطب',
        'finished' => 'مشطب',
        'furnished' => 'مفروش',
    ];

    /**
     * Every column a user may submit from the create/edit forms.
     *
     * @var array<int, string>
     */
    public const EDITABLE_FIELDS = [
        'title',
        'description',
        'property_type_id',
        'purpose',
        'governorate_id',
        'city_id',
        'district_id',
        'price',
        'currency',
        'area',
        'bedrooms',
        'bathrooms',
        'floor',
        'total_floors',
        'parking_spaces',
        'year_built',
        'finishing',
        'available_from',
        'address',
        'contact_name',
        'contact_phone',
        'contact_whatsapp',
    ];

    /**
     * Changes to these fields alter the public listing itself. A published
     * property that receives one of these changes must return to review.
     *
     * @var array<int, string>
     */
    public const SUBSTANTIVE_FIELDS = [
        'title',
        'description',
        'property_type_id',
        'purpose',
        'governorate_id',
        'city_id',
        'district_id',
        'price',
        'currency',
        'area',
        'bedrooms',
        'bathrooms',
        'floor',
        'total_floors',
        'parking_spaces',
        'year_built',
        'finishing',
        'available_from',
        'address',
    ];

    /**
     * Minimal rules for storing a draft. The database requires a title,
     * type, governorate, price and area, so drafts enforce those too.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function draft(?int $governorateId, ?int $cityId): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')],
            'purpose' => ['required', Rule::enum(PropertyPurpose::class)],
            'governorate_id' => ['required', 'integer', Rule::exists('governorates', 'id')],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->where('governorate_id', $governorateId)],
            'district_id' => ['nullable', 'integer', Rule::exists('districts', 'id')->where('city_id', $cityId)],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', Rule::in(self::CURRENCIES)],
            'area' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
            'total_floors' => ['nullable', 'integer', 'min:0', 'max:200'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:100'],
            'year_built' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'finishing' => ['nullable', 'string', Rule::in(array_keys(self::FINISHINGS))],
            'available_from' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'contact_whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
        ];
    }

    /**
     * Full rules required before a property may enter the review queue.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function submission(?int $governorateId, ?int $cityId): array
    {
        $rules = self::draft($governorateId, $cityId);

        $rules['description'] = ['required', 'string', 'min:10', 'max:5000'];

        return $rules;
    }
}
