<?php

namespace App\Livewire\Properties;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Support\PropertyFormRules;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.account')]
#[Title('عقاراتي')]
class MyProperties extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    #[Url(except: '')]
    public string $purpose = '';

    #[Url(as: 'type', except: '')]
    public string $type = '';

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'purpose', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'statusFilter', 'purpose', 'type');
        $this->resetPage();
    }

    public function duplicate(int $propertyId): void
    {
        $this->resetActionState();

        $user = $this->activeUser();
        $original = $this->ownedProperty($propertyId);

        // Explicit copy of user-editable columns only. Images, amenities,
        // publishing metadata and moderation data are intentionally excluded.
        $copy = new Property;

        foreach (PropertyFormRules::EDITABLE_FIELDS as $field) {
            $copy->{$field} = $original->getAttribute($field);
        }

        $copy->user_id = $user->getKey();
        $copy->status = PropertyStatus::Draft;
        $copy->slug = null; // regenerated as a new unique slug by the model.
        $copy->is_featured = false;
        $copy->published_at = null;
        $copy->published_by = null;
        $copy->views_count = 0;

        $copy->save();

        $this->actionMessage = 'تم نسخ العقار كمسودة جديدة.';
    }

    public function archive(int $propertyId): void
    {
        $this->resetActionState();

        $property = $this->ownedProperty($propertyId);

        if ($property->status === PropertyStatus::Archived) {
            $this->actionError = 'هذا العقار مؤرشف بالفعل.';

            return;
        }

        $property->status = PropertyStatus::Archived;
        $property->save();

        $this->actionMessage = 'تم أرشفة العقار.';
    }

    public function reactivate(int $propertyId): void
    {
        $this->resetActionState();

        $property = $this->ownedProperty($propertyId);

        if ($property->status !== PropertyStatus::Archived) {
            $this->actionError = 'لا يمكن إعادة تفعيل عقار غير مؤرشف.';

            return;
        }

        if (! Gate::allows('publish-properties')) {
            $this->actionError = 'حسابك غير موثق. أكمل توثيق حسابك قبل إعادة تفعيل العقار.';

            return;
        }

        $rules = PropertyFormRules::submission($property->governorate_id, $property->city_id);

        $validator = Validator::make($property->only(array_keys($rules)), $rules);

        if ($validator->fails()) {
            $this->actionError = 'أكمل بيانات العقار المطلوبة قبل إعادة تفعيله وإرساله للمراجعة.';

            return;
        }

        // Reactivation never republishes directly: it re-enters the review queue.
        $property->status = PropertyStatus::Pending;
        $property->save();

        $this->actionMessage = 'تم إرسال العقار للمراجعة. لن يُنشر إلا بعد اعتماد الإدارة.';
    }

    public function markSold(int $propertyId): void
    {
        $this->transitionTo($propertyId, PropertyStatus::Sold, 'تم تحديد العقار كمباع.');
    }

    public function markRented(int $propertyId): void
    {
        $this->transitionTo($propertyId, PropertyStatus::Rented, 'تم تحديد العقار كمؤجَّر.');
    }

    public function delete(int $propertyId): void
    {
        $this->resetActionState();

        $this->activeUser();

        $property = Property::query()->findOrFail($propertyId);

        Gate::authorize('delete', $property);

        // Soft delete only: the record and its relations remain recoverable.
        $property->delete();

        $this->actionMessage = 'تم حذف العقار.';
    }

    public function render(): View
    {
        $this->activeUser();

        $properties = Property::query()
            ->ownedBy(auth()->user())
            ->with(['coverImage', 'type', 'governorate', 'city', 'district'])
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('address', 'like', $term);
                });
            })
            ->when($this->statusFilter !== '', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->purpose !== '', fn (Builder $query) => $query->where('purpose', $this->purpose))
            ->when($this->type !== '', fn (Builder $query) => $query->whereHas('type', fn (Builder $query) => $query->where('slug', $this->type)))
            ->latest()
            ->paginate(10);

        return view('livewire.properties.my-properties', [
            'properties' => $properties,
            'propertyTypes' => PropertyType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'statuses' => PropertyStatus::cases(),
            'purposes' => PropertyPurpose::cases(),
        ]);
    }

    private function transitionTo(int $propertyId, PropertyStatus $target, string $message): void
    {
        $this->resetActionState();

        $property = $this->ownedProperty($propertyId);

        if ($property->status !== PropertyStatus::Published) {
            $this->actionError = 'لا يمكن تغيير حالة العقار إلا إذا كان منشورًا.';

            return;
        }

        $property->status = $target;
        $property->save();

        $this->actionMessage = $message;
    }

    private function ownedProperty(int $propertyId): Property
    {
        $this->activeUser();

        $property = Property::query()->findOrFail($propertyId);

        Gate::authorize('update', $property);

        return $property;
    }

    private function activeUser(): User
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_if($user === null || $user->isSuspended(), 403);

        return $user;
    }

    private function resetActionState(): void
    {
        $this->actionMessage = null;
        $this->actionError = null;
    }
}
