<?php

namespace App\Livewire\Services;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use App\Services\ServiceRequestWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('تفاصيل طلب الخدمة')]
class ServiceRequestDetails extends Component
{
    public ServiceRequest $serviceRequest;

    public int $rating = 5;

    public string $comment = '';

    public string $reportReason = '';

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    public function mount(ServiceRequest $serviceRequest): void
    {
        Gate::authorize('view', $serviceRequest);

        $this->serviceRequest = $serviceRequest->load([
            'category',
            'provider.user',
            'provider.categories',
            'governorate',
            'city',
            'district',
            'images',
            'review',
            'commission',
        ]);
    }

    public function cancelRequest(): void
    {
        $this->resetFeedback();

        Gate::authorize('cancel', $this->serviceRequest);

        try {
            app(ServiceRequestWorkflow::class)->cancel($this->serviceRequest, auth()->user(), 'ألغى العميل الطلب.');
            $this->serviceRequest->refresh()->load(['provider.user', 'category', 'governorate', 'city', 'images', 'review']);
            $this->actionMessage = 'تم إلغاء الطلب.';
        } catch (ValidationException $exception) {
            $this->actionError = $exception->validator->errors()->first();
        }
    }

    public function submitReview(): void
    {
        $this->resetFeedback();

        Gate::authorize('review', $this->serviceRequest);

        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->serviceRequest->service_provider_id === null) {
            $this->actionError = 'لا يمكن تقييم طلب بدون مقدم خدمة.';

            return;
        }

        DB::transaction(function (): void {
            ServiceReview::create([
                'service_request_id' => $this->serviceRequest->getKey(),
                'service_provider_id' => $this->serviceRequest->service_provider_id,
                'user_id' => auth()->id(),
                'rating' => $this->rating,
                'comment' => $this->comment ?: null,
            ]);

            $this->serviceRequest->provider?->recalculateRating();
        });

        $this->serviceRequest->refresh()->load('review', 'provider');
        $this->actionMessage = 'شكرًا لك، تم نشر تقييمك.';
    }

    public function reportReview(): void
    {
        $this->resetFeedback();

        $review = $this->serviceRequest->review;

        if ($review === null) {
            return;
        }

        Gate::authorize('report', $review);

        $this->validate([
            'reportReason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reportReason.min' => 'اشرح المشكلة في 10 أحرف على الأقل.',
        ]);

        $review->forceFill([
            'is_reported' => true,
            'report_reason' => $this->reportReason,
            'reported_at' => now(),
        ])->save();

        $this->serviceRequest->refresh()->load('review');
        $this->actionMessage = 'تم إرسال البلاغ إلى الإدارة للمراجعة.';
    }

    public function render(): View
    {
        return view('livewire.services.service-request-details', [
            'timeline' => $this->timeline(),
            'statuses' => ServiceRequestStatus::cases(),
        ]);
    }

    /**
     * @return array<int, array{label: string, at: mixed, done: bool}>
     */
    private function timeline(): array
    {
        return [
            ['label' => 'تم إنشاء الطلب', 'at' => $this->serviceRequest->created_at, 'done' => true],
            ['label' => 'تم الإسناد لمقدم خدمة', 'at' => $this->serviceRequest->assigned_at, 'done' => $this->serviceRequest->assigned_at !== null],
            ['label' => 'قبول مقدم الخدمة', 'at' => $this->serviceRequest->accepted_at, 'done' => $this->serviceRequest->accepted_at !== null],
            ['label' => 'بدء التنفيذ', 'at' => $this->serviceRequest->started_at, 'done' => $this->serviceRequest->started_at !== null],
            ['label' => 'اكتمال الخدمة', 'at' => $this->serviceRequest->completed_at, 'done' => $this->serviceRequest->completed_at !== null],
        ];
    }

    private function resetFeedback(): void
    {
        $this->actionMessage = null;
        $this->actionError = null;
        $this->resetErrorBag();
    }
}
