<?php

namespace Tests\Feature\Services;

use App\Livewire\Services\ServiceRequestDetails;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceReviewTest extends TestCase
{
    use RefreshDatabase;

    private function completedRequest(?User $customer = null): array
    {
        $customer ??= User::factory()->create();
        $provider = ServiceProvider::factory()->create();

        $request = ServiceRequest::factory()->completed()->create([
            'user_id' => $customer->id,
            'service_provider_id' => $provider->id,
        ]);

        return [$request, $customer, $provider];
    }

    public function test_customer_can_review_a_completed_request(): void
    {
        [$request, $customer, $provider] = $this->completedRequest();

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 4)
            ->set('comment', 'خدمة ممتازة والالتزام بالموعد.')
            ->call('submitReview')
            ->assertHasNoErrors()
            ->assertSet('actionMessage', 'شكرًا لك، تم نشر تقييمك.');

        $review = ServiceReview::query()->firstOrFail();

        $this->assertSame($request->id, $review->service_request_id);
        $this->assertSame($provider->id, $review->service_provider_id);
        $this->assertSame($customer->id, $review->user_id);
        $this->assertSame(4, $review->rating);
        $this->assertSame('خدمة ممتازة والالتزام بالموعد.', $review->comment);
    }

    public function test_provider_rating_is_recalculated_after_a_review(): void
    {
        [$request, $customer, $provider] = $this->completedRequest();

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 5)
            ->call('submitReview')
            ->assertHasNoErrors();

        $provider->refresh();

        $this->assertSame(1, $provider->reviews_count);
        $this->assertEquals(5, (float) $provider->rating_avg);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        [$request, $customer] = $this->completedRequest();

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 0)
            ->call('submitReview')
            ->assertHasErrors(['rating']);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 6)
            ->call('submitReview')
            ->assertHasErrors(['rating']);

        $this->assertSame(0, ServiceReview::query()->count());
    }

    public function test_request_cannot_be_reviewed_twice(): void
    {
        [$request, $customer, $provider] = $this->completedRequest();

        ServiceReview::factory()->create([
            'service_request_id' => $request->id,
            'service_provider_id' => $provider->id,
            'user_id' => $customer->id,
            'rating' => 3,
        ]);

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request->refresh()])
            ->set('rating', 5)
            ->call('submitReview')
            ->assertForbidden();

        $this->assertSame(1, ServiceReview::query()->count());
    }

    public function test_customer_cannot_review_an_unfinished_request(): void
    {
        $customer = User::factory()->create();
        $provider = ServiceProvider::factory()->create();
        $request = ServiceRequest::factory()->inProgress()->create([
            'user_id' => $customer->id,
            'service_provider_id' => $provider->id,
        ]);

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 5)
            ->call('submitReview')
            ->assertForbidden();

        $this->assertSame(0, ServiceReview::query()->count());
    }

    public function test_another_user_cannot_review_someone_elses_request(): void
    {
        [$request] = $this->completedRequest();
        $other = User::factory()->create();

        $this->actingAs($other);

        // Even loading the page is forbidden because the other user has no
        // relationship to the request.
        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->assertForbidden();
    }

    public function test_customer_can_report_their_own_review(): void
    {
        [$request, $customer, $provider] = $this->completedRequest();

        $review = ServiceReview::factory()->create([
            'service_request_id' => $request->id,
            'service_provider_id' => $provider->id,
            'user_id' => $customer->id,
            'rating' => 2,
        ]);

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request->refresh()])
            ->set('reportReason', 'التقييم يحتوي على معلومات غير صحيحة عن الخدمة.')
            ->call('reportReview')
            ->assertHasNoErrors()
            ->assertSet('actionMessage', 'تم إرسال البلاغ إلى الإدارة للمراجعة.');

        $review->refresh();

        $this->assertTrue($review->is_reported);
        $this->assertSame('التقييم يحتوي على معلومات غير صحيحة عن الخدمة.', $review->report_reason);
        $this->assertNotNull($review->reported_at);
    }

    public function test_report_reason_must_be_at_least_ten_characters(): void
    {
        [$request, $customer, $provider] = $this->completedRequest();

        ServiceReview::factory()->create([
            'service_request_id' => $request->id,
            'service_provider_id' => $provider->id,
            'user_id' => $customer->id,
        ]);

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request->refresh()])
            ->set('reportReason', 'قصير')
            ->call('reportReview')
            ->assertHasErrors(['reportReason']);
    }

    public function test_another_user_cannot_report_a_review(): void
    {
        [$request, , $provider] = $this->completedRequest();

        $review = ServiceReview::factory()->create([
            'service_request_id' => $request->id,
            'service_provider_id' => $provider->id,
            'user_id' => $request->user_id,
        ]);

        $other = User::factory()->create();

        $this->actingAs($other);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request->refresh()])
            ->assertForbidden();
    }

    public function test_review_is_not_created_when_the_request_has_no_provider(): void
    {
        $customer = User::factory()->create();
        $request = ServiceRequest::factory()->completed()->create([
            'user_id' => $customer->id,
            'service_provider_id' => null,
        ]);

        $this->actingAs($customer);

        Livewire::test(ServiceRequestDetails::class, ['serviceRequest' => $request])
            ->set('rating', 5)
            ->call('submitReview')
            ->assertSet('actionError', 'لا يمكن تقييم طلب بدون مقدم خدمة.');

        $this->assertSame(0, ServiceReview::query()->count());
    }
}
