<?php

namespace App\Livewire\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
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
#[Title('طلبات العملاء')]
class ProviderRequests extends Component
{
    use WithPagination;

    public ?ServiceProvider $provider = null;

    #[Url(except: '')]
    public string $statusFilter = '';

    public string $rejectReason = '';

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    public function mount(): void
    {
        $this->provider = auth()->user()->serviceProvider;

        abort_if($this->provider === null, 403, 'أنشئ حساب مقدم خدمة أولًا.');

        Gate::authorize('view', $this->provider);
    }

    public function updating(string $property): void
    {
        if ($property === 'statusFilter') {
            $this->resetPage();
        }
    }

    public function accept(int $requestId): void
    {
        $this->runAction($requestId, fn (ServiceRequest $serviceRequest): ServiceRequest => app(ServiceRequestWorkflow::class)
            ->accept($serviceRequest, auth()->user()), 'تم قبول الطلب.');
    }

    public function reject(int $requestId): void
    {
        $this->validate([
            'rejectReason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->runAction($requestId, fn (ServiceRequest $serviceRequest): ServiceRequest => app(ServiceRequestWorkflow::class)
            ->reject($serviceRequest, auth()->user(), $this->rejectReason ?: null), 'تم رفض الطلب وإعادته للإدارة.');
    }

    public function start(int $requestId): void
    {
        $this->runAction($requestId, fn (ServiceRequest $serviceRequest): ServiceRequest => app(ServiceRequestWorkflow::class)
            ->start($serviceRequest, auth()->user()), 'تم بدء تنفيذ الطلب.');
    }

    public function complete(int $requestId): void
    {
        $this->runAction($requestId, fn (ServiceRequest $serviceRequest): ServiceRequest => app(ServiceRequestWorkflow::class)
            ->complete($serviceRequest, auth()->user()), 'تم إكمال الطلب. شكرًا لك.');
    }

    public function render(): View
    {
        $requests = $this->provider->requests()
            ->with(['customer', 'category', 'governorate', 'city', 'review'])
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('assigned_at')
            ->latest()
            ->paginate(8);

        $counts = $this->provider->requests()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.services.provider-requests', [
            'requests' => $requests,
            'statuses' => ServiceRequestStatus::cases(),
            'counts' => $counts,
        ]);
    }

    /**
     * @param  callable(ServiceRequest): ServiceRequest  $callback
     */
    private function runAction(int $requestId, callable $callback, string $successMessage): void
    {
        $this->actionMessage = null;
        $this->actionError = null;

        $serviceRequest = $this->provider->requests()->findOrFail($requestId);

        Gate::authorize('manageStatus', $serviceRequest);

        try {
            $callback($serviceRequest);
            $this->actionMessage = $successMessage;
            $this->rejectReason = '';
        } catch (ValidationException $exception) {
            $this->actionError = $exception->validator->errors()->first();
        }
    }
}
