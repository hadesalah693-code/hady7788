<?php

namespace App\Livewire\Services;

use App\Enums\ServiceRequestStatus;
use App\Services\ServiceRequestWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.account')]
#[Title('طلبات الخدمات')]
class MyServiceRequests extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $statusFilter = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    public function updating(string $property): void
    {
        if (in_array($property, ['statusFilter', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('statusFilter', 'search');
        $this->resetPage();
    }

    public function cancel(int $requestId): void
    {
        $this->actionMessage = null;
        $this->actionError = null;

        $serviceRequest = auth()->user()->serviceRequests()->findOrFail($requestId);

        Gate::authorize('cancel', $serviceRequest);

        try {
            app(ServiceRequestWorkflow::class)->cancel($serviceRequest, auth()->user(), 'ألغى العميل الطلب.');
            $this->actionMessage = 'تم إلغاء الطلب.';
        } catch (ValidationException $exception) {
            $this->actionError = $exception->validator->errors()->first();
        }
    }

    public function render(): View
    {
        $requests = auth()->user()->serviceRequests()
            ->with(['category', 'provider', 'governorate', 'city', 'review'])
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';

                $query->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name_ar', 'like', $term));
                });
            })
            ->latest()
            ->paginate(8);

        return view('livewire.services.my-service-requests', [
            'requests' => $requests,
            'statuses' => ServiceRequestStatus::cases(),
            'openCount' => auth()->user()->serviceRequests()->open()->count(),
        ]);
    }
}
