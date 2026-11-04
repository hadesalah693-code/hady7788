<?php

namespace App\Livewire\Properties;

use App\Enums\PropertyStatus;
use App\Livewire\Properties\Concerns\ManagesPropertyForm;
use App\Models\Property;
use App\Support\PropertyFormRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('تعديل العقار')]
class EditProperty extends Component
{
    use ManagesPropertyForm;

    public int $propertyId;

    public ?string $savedMessage = null;

    public ?string $errorMessage = null;

    public function mount(string $property): void
    {
        $property = (int) $property;
        $model = $this->authorizedProperty($property);

        $this->propertyId = $model->getKey();
        $this->fillFromProperty($model);
    }

    public function save(): void
    {
        $this->savedMessage = null;
        $this->errorMessage = null;

        $property = $this->authorizedProperty($this->propertyId);

        $returnsToReview = $property->status === PropertyStatus::Published
            && $this->hasSubstantiveChanges($property);

        // A published listing that changes materially must be re-reviewed with
        // complete data before it can appear publicly again.
        $this->validate($returnsToReview ? $this->submitRules() : $this->draftRules());

        $this->applyToProperty($property);

        if ($returnsToReview) {
            $property->status = PropertyStatus::Pending;
            $this->savedMessage = 'تم حفظ التعديلات وإعادة العقار للمراجعة. لن تظهر التعديلات للعامة قبل اعتماد الإدارة.';
        } else {
            $this->savedMessage = 'تم حفظ التعديلات بنجاح.';
        }

        $property->save();

        $this->fillFromProperty($property->refresh());
    }

    public function submitForReview(): void
    {
        $this->savedMessage = null;
        $this->errorMessage = null;

        Gate::authorize('publish-properties');

        $property = $this->authorizedProperty($this->propertyId);

        if (! in_array($property->status, [PropertyStatus::Draft, PropertyStatus::Rejected], true)) {
            $this->errorMessage = 'لا يمكن إرسال هذا العقار للمراجعة في حالته الحالية.';

            return;
        }

        $this->validate($this->submitRules());

        $this->applyToProperty($property);
        $property->status = PropertyStatus::Pending;
        $property->save();

        $this->savedMessage = 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.';
        $this->fillFromProperty($property->refresh());
    }

    public function render(): View
    {
        $property = $this->authorizedProperty($this->propertyId);

        return view('livewire.properties.edit-property', array_merge($this->formOptions(), [
            'property' => $property,
            'canSubmit' => in_array($property->status, [PropertyStatus::Draft, PropertyStatus::Rejected], true),
        ]));
    }

    private function authorizedProperty(int $propertyId): Property
    {
        $user = auth()->user();

        abort_if($user === null || $user->isSuspended(), 403);

        $property = Property::query()->findOrFail($propertyId);

        Gate::authorize('update', $property);

        return $property;
    }

    private function hasSubstantiveChanges(Property $property): bool
    {
        foreach (PropertyFormRules::SUBSTANTIVE_FIELDS as $field) {
            $current = $this->comparable($field, $property->getAttribute($field));
            $submitted = $this->comparable($field, $this->{$field});

            if ($current !== $submitted) {
                return true;
            }
        }

        return false;
    }

    private function comparable(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (in_array($field, ['price', 'area'], true)) {
            return number_format((float) $value, 2, '.', '');
        }

        if (in_array($field, [
            'property_type_id', 'governorate_id', 'city_id', 'district_id',
            'bedrooms', 'bathrooms', 'floor', 'total_floors', 'parking_spaces', 'year_built',
        ], true)) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }
}
