<?php

namespace App\Livewire\Properties;

use App\Enums\AmenityCategory;
use App\Enums\PropertyStatus;
use App\Livewire\Properties\Concerns\AuthorizesPropertyMedia;
use App\Models\Amenity;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PropertyAmenities extends Component
{
    use AuthorizesPropertyMedia;

    /** @var array<int, int|string> */
    public array $selected = [];

    public ?string $message = null;

    public ?string $error = null;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;

        $property = $this->mediaProperty();

        $this->selected = $property->amenities()
            ->where('amenities.is_active', true)
            ->pluck('amenities.id')
            ->map(fn ($id): string => (string) $id)
            ->all();
    }

    public function save(): void
    {
        $this->message = null;
        $this->error = null;

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        $this->validate([
            'selected' => ['array', 'max:50'],
            'selected.*' => [
                'integer',
                'distinct',
                Rule::exists('amenities', 'id')->where('is_active', true),
            ],
        ], [
            'selected.array' => 'تعذر قراءة المرافق المحددة.',
            'selected.max' => 'عدد المرافق المحددة أكبر من المسموح.',
            'selected.*.integer' => 'أحد المرافق المحددة غير صالح.',
            'selected.*.distinct' => 'لا يمكن تكرار نفس المرفق.',
            'selected.*.exists' => 'أحد المرافق المحددة غير متاح.',
        ]);

        $ids = array_values(array_unique(array_map('intval', $this->selected)));

        DB::transaction(function () use ($property, $ids): void {
            $locked = Property::query()->lockForUpdate()->findOrFail($property->getKey());

            $changes = $locked->amenities()->sync($ids);

            $changed = $changes['attached'] !== [] || $changes['detached'] !== [];

            if ($changed && $locked->status === PropertyStatus::Published) {
                $locked->status = PropertyStatus::Pending;
                $locked->save();
            }
        });

        $this->selected = array_map('strval', $ids);
        $this->message = 'تم حفظ المرافق بنجاح.';
    }

    public function render(): View
    {
        $property = $this->mediaProperty();

        $amenities = Amenity::query()
            ->active()
            ->ordered()
            ->get()
            ->groupBy(fn (Amenity $amenity): string => $amenity->category?->value ?? 'other');

        return view('livewire.properties.property-amenities', [
            'property' => $property,
            'categories' => AmenityCategory::cases(),
            'amenitiesByCategory' => $amenities,
            'canManage' => $this->canManage($property),
        ]);
    }
}
