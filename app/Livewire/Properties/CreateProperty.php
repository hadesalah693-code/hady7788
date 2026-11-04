<?php

namespace App\Livewire\Properties;

use App\Enums\PropertyStatus;
use App\Livewire\Properties\Concerns\ManagesPropertyForm;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('إضافة عقار جديد')]
class CreateProperty extends Component
{
    use ManagesPropertyForm;

    public ?string $savedMessage = null;

    public ?int $savedPropertyId = null;

    public function mount(): void
    {
        Gate::authorize('create', Property::class);

        abort_if(auth()->user()?->isSuspended(), 403);
    }

    public function saveDraft(): void
    {
        $this->savedMessage = null;
        $this->savedPropertyId = null;

        $this->validate($this->draftRules());

        $property = new Property;

        $this->applyToProperty($property);

        $property->user_id = auth()->id();
        $property->status = PropertyStatus::Draft;
        $property->is_featured = false;
        $property->published_at = null;
        $property->published_by = null;
        $property->views_count = 0;

        $property->save();

        $this->savedPropertyId = $property->getKey();
        $this->savedMessage = 'تم حفظ المسودة بنجاح.';
        $this->resetFormFields();
    }

    public function submitForReview(): void
    {
        $this->savedMessage = null;
        $this->savedPropertyId = null;

        Gate::authorize('publish-properties');

        $this->validate($this->submitRules());

        $property = new Property;

        $this->applyToProperty($property);

        $property->user_id = auth()->id();
        $property->status = PropertyStatus::Pending;
        $property->is_featured = false;
        $property->published_at = null;
        $property->published_by = null;
        $property->views_count = 0;

        $property->save();

        $this->savedPropertyId = $property->getKey();
        $this->savedMessage = 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.';
        $this->resetFormFields();
    }

    public function render(): View
    {
        return view('livewire.properties.create-property', $this->formOptions());
    }
}
