<?php

namespace App\Livewire\Properties;

use App\Enums\PropertyPurpose;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PropertyList extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $purpose = '';

    #[Url(as: 'type', except: '')]
    public string $type = '';

    #[Url(as: 'governorate', except: '')]
    public string $governorate = '';

    #[Url(except: 'latest')]
    public string $sort = 'latest';

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'purpose', 'type', 'governorate', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'purpose', 'type', 'governorate', 'sort');
        $this->resetPage();
    }

    public function render(): View
    {
        $properties = Property::query()
            ->published()
            ->with(['coverImage', 'city', 'district', 'governorate', 'type'])
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('city', fn (Builder $query) => $query->where('name_ar', 'like', $term))
                        ->orWhereHas('district', fn (Builder $query) => $query->where('name_ar', 'like', $term))
                        ->orWhereHas('governorate', fn (Builder $query) => $query->where('name_ar', 'like', $term));
                });
            })
            ->when($this->purpose !== '', fn (Builder $query) => $query->where('purpose', $this->purpose))
            ->when($this->type !== '', fn (Builder $query) => $query->whereHas('type', fn (Builder $query) => $query->where('slug', $this->type)))
            ->when($this->governorate !== '', fn (Builder $query) => $query->whereHas('governorate', fn (Builder $query) => $query->where('slug', $this->governorate)))
            ->when($this->sort === 'price_asc', fn (Builder $query) => $query->orderBy('price'))
            ->when($this->sort === 'price_desc', fn (Builder $query) => $query->orderByDesc('price'))
            ->when($this->sort === 'area_desc', fn (Builder $query) => $query->orderByDesc('area'))
            ->when(! in_array($this->sort, ['price_asc', 'price_desc', 'area_desc'], true), fn (Builder $query) => $query->latest('published_at'))
            ->paginate(9);

        return view('livewire.properties.property-list', [
            'properties' => $properties,
            'propertyTypes' => PropertyType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'governorates' => Governorate::query()->where('is_active', true)->orderBy('name_ar')->get(),
            'purposes' => PropertyPurpose::cases(),
        ])
            ->title(__('app.listing.title').' | '.__('app.brand'))
            ->layoutData(['metaDescription' => __('app.listing.subtitle')]);
    }
}
