<?php

namespace App\Livewire\Services;

use App\Models\Governorate;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('خدمات الإمداد')]
class ServiceCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'category', except: '')]
    public string $category = '';

    #[Url(as: 'governorate', except: '')]
    public string $governorate = '';

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'category', 'governorate'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'governorate');
        $this->resetPage();
    }

    public function render(): View
    {
        $categories = ServiceCategory::query()
            ->active()
            ->ordered()
            ->withCount(['providers' => fn ($query) => $query->available()])
            ->get();

        $governorates = Governorate::query()
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();

        $providers = ServiceProvider::query()
            ->available()
            ->with(['categories', 'governorate', 'city', 'images'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';

                $query->where(function ($inner) use ($term): void {
                    $inner->where('display_name', 'like', $term)
                        ->orWhere('bio', 'like', $term)
                        ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name_ar', 'like', $term));
                });
            })
            ->when($this->category !== '', fn ($query) => $query->whereHas(
                'categories',
                fn ($categoryQuery) => $categoryQuery->where('slug', $this->category),
            ))
            ->when($this->governorate !== '', fn ($query) => $query->whereHas(
                'governorate',
                fn ($governorateQuery) => $governorateQuery->where('slug', $this->governorate),
            ))
            ->orderByDesc('rating_avg')
            ->orderByDesc('reviews_count')
            ->orderByDesc('id')
            ->paginate(9);

        $activeCategory = $this->category !== ''
            ? $categories->firstWhere('slug', $this->category)
            : null;

        return view('livewire.services.service-catalog', [
            'categories' => $categories,
            'governorates' => $governorates,
            'providers' => $providers,
            'activeCategory' => $activeCategory,
            'providersCount' => ServiceProvider::query()->available()->count(),
            'completedCount' => (int) ServiceProvider::query()->sum('completed_requests_count'),
        ]);
    }
}
