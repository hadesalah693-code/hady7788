<?php

namespace App\Livewire\Properties\Concerns;

use App\Enums\PropertyPurpose;
use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\PropertyFormRules;

/**
 * Shared create/edit property form state.
 *
 * Only user-editable columns are exposed as public Livewire properties.
 * Administrative columns never appear here, so they can never be set from
 * the browser payload.
 */
trait ManagesPropertyForm
{
    public ?string $title = null;

    public ?string $description = null;

    public ?int $property_type_id = null;

    public string $purpose = 'sale';

    public ?int $governorate_id = null;

    public ?int $city_id = null;

    public ?int $district_id = null;

    public ?string $price = null;

    public string $currency = 'EGP';

    public ?string $area = null;

    public ?int $bedrooms = null;

    public ?int $bathrooms = null;

    public ?int $floor = null;

    public ?int $total_floors = null;

    public ?int $parking_spaces = null;

    public ?int $year_built = null;

    public ?string $finishing = null;

    public ?string $available_from = null;

    public ?string $address = null;

    public ?string $contact_name = null;

    public ?string $contact_phone = null;

    public ?string $contact_whatsapp = null;

    public function updatedGovernorateId(): void
    {
        $this->city_id = null;
        $this->district_id = null;
    }

    public function updatedCityId(): void
    {
        $this->district_id = null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function draftRules(): array
    {
        return PropertyFormRules::draft($this->governorate_id, $this->city_id);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function submitRules(): array
    {
        return PropertyFormRules::submission($this->governorate_id, $this->city_id);
    }

    protected function fillFromProperty(Property $property): void
    {
        $this->title = $property->title;
        $this->description = $property->description;
        $this->property_type_id = $property->property_type_id;
        $this->purpose = $property->purpose?->value ?? PropertyPurpose::Sale->value;
        $this->governorate_id = $property->governorate_id;
        $this->city_id = $property->city_id;
        $this->district_id = $property->district_id;
        $this->price = $property->price !== null ? (string) $property->price : null;
        $this->currency = $property->currency ?: 'EGP';
        $this->area = $property->area !== null ? (string) $property->area : null;
        $this->bedrooms = $property->bedrooms;
        $this->bathrooms = $property->bathrooms;
        $this->floor = $property->floor;
        $this->total_floors = $property->total_floors;
        $this->parking_spaces = $property->parking_spaces;
        $this->year_built = $property->year_built;
        $this->finishing = $property->finishing;
        $this->available_from = $property->available_from?->toDateString();
        $this->address = $property->address;
        $this->contact_name = $property->contact_name;
        $this->contact_phone = $property->contact_phone;
        $this->contact_whatsapp = $property->contact_whatsapp;
    }

    /**
     * Explicitly copy only user-editable values onto the model.
     *
     * This method never touches user_id, status, is_featured, published_at,
     * published_by, views_count, latitude or longitude.
     */
    protected function applyToProperty(Property $property): void
    {
        $property->title = $this->title;
        $property->description = $this->description ?? '';
        $property->property_type_id = $this->property_type_id;
        $property->purpose = PropertyPurpose::from($this->purpose);
        $property->governorate_id = $this->governorate_id;
        $property->city_id = $this->city_id;
        $property->district_id = $this->district_id;
        $property->price = $this->price;
        $property->currency = $this->currency;
        $property->area = $this->area;
        $property->bedrooms = $this->bedrooms;
        $property->bathrooms = $this->bathrooms;
        $property->floor = $this->floor;
        $property->total_floors = $this->total_floors;
        $property->parking_spaces = $this->parking_spaces;
        $property->year_built = $this->year_built;
        $property->finishing = $this->finishing;
        $property->available_from = $this->available_from ?: null;
        $property->address = $this->address;
        $property->contact_name = $this->contact_name;
        $property->contact_phone = $this->contact_phone;
        $property->contact_whatsapp = $this->contact_whatsapp;
    }

    protected function resetFormFields(): void
    {
        $this->reset([
            'title', 'description', 'property_type_id', 'governorate_id', 'city_id', 'district_id',
            'price', 'area', 'bedrooms', 'bathrooms', 'floor', 'total_floors', 'parking_spaces',
            'year_built', 'finishing', 'available_from', 'address', 'contact_name', 'contact_phone',
            'contact_whatsapp',
        ]);

        $this->purpose = 'sale';
        $this->currency = 'EGP';
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'propertyTypes' => PropertyType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'governorates' => Governorate::query()->where('is_active', true)->orderBy('name_ar')->get(),
            'cities' => $this->governorate_id
                ? City::query()->where('governorate_id', $this->governorate_id)->orderBy('name_ar')->get()
                : collect(),
            'districts' => $this->city_id
                ? District::query()->where('city_id', $this->city_id)->orderBy('name_ar')->get()
                : collect(),
            'finishings' => PropertyFormRules::FINISHINGS,
            'currencies' => array_combine(PropertyFormRules::CURRENCIES, PropertyFormRules::CURRENCIES),
        ];
    }
}
