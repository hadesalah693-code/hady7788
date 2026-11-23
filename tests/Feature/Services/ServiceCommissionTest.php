<?php

namespace Tests\Feature\Services;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Models\CommissionSetting;
use App\Models\ServiceCommission;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceCommissionService;
use App\Services\ServiceRequestWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCommissionTest extends TestCase
{
    use RefreshDatabase;

    private function commissions(): ServiceCommissionService
    {
        return app(ServiceCommissionService::class);
    }

    private function completedRequest(?ServiceProvider $provider = null, ?float $quotedPrice = 1000): ServiceRequest
    {
        return ServiceRequest::factory()->completed()->create([
            'service_provider_id' => ($provider ?? ServiceProvider::factory()->create())->id,
            'quoted_price' => $quotedPrice,
        ]);
    }

    public function test_fixed_commission_is_created_for_a_completed_request(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 75,
            'is_active' => true,
        ]);

        $request = $this->completedRequest();

        $commission = $this->commissions()->syncFor($request);

        $this->assertNotNull($commission);
        $this->assertSame(CommissionType::Fixed, $commission->type);
        $this->assertEquals(75, (float) $commission->rate);
        $this->assertEquals(75, (float) $commission->amount);
        $this->assertSame(CommissionStatus::Pending, $commission->status);
        $this->assertSame($request->service_provider_id, $commission->service_provider_id);
    }

    public function test_percentage_commission_uses_the_quoted_service_value(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Percentage,
            'percentage_rate' => 10,
            'min_amount' => 0,
            'is_active' => true,
        ]);

        $commission = $this->commissions()->syncFor($this->completedRequest(quotedPrice: 1000));

        $this->assertNotNull($commission);
        $this->assertSame(CommissionType::Percentage, $commission->type);
        $this->assertEquals(10, (float) $commission->rate);
        $this->assertEquals(100, (float) $commission->amount);
    }

    public function test_percentage_commission_respects_the_minimum_amount(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Percentage,
            'percentage_rate' => 5,
            'min_amount' => 150,
            'is_active' => true,
        ]);

        $commission = $this->commissions()->syncFor($this->completedRequest(quotedPrice: 1000));

        $this->assertNotNull($commission);
        $this->assertEquals(150, (float) $commission->amount);
    }

    public function test_fixed_commission_ignores_the_service_value(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 60,
            'is_active' => true,
        ]);

        $commission = $this->commissions()->syncFor($this->completedRequest(quotedPrice: 99999));

        $this->assertNotNull($commission);
        $this->assertEquals(60, (float) $commission->amount);
    }

    public function test_config_defaults_are_used_when_no_setting_exists(): void
    {
        config([
            'service_module.commission.type' => CommissionType::Fixed->value,
            'service_module.commission.fixed_amount' => 45,
            'service_module.commission.percentage_rate' => 5,
            'service_module.commission.min_amount' => 0,
        ]);

        $commission = $this->commissions()->syncFor($this->completedRequest(quotedPrice: null));

        $this->assertNotNull($commission);
        $this->assertEquals(45, (float) $commission->amount);
    }

    public function test_commission_is_not_created_for_an_unfinished_request(): void
    {
        $request = ServiceRequest::factory()->inProgress()->create();

        $this->assertNull($this->commissions()->syncFor($request));
        $this->assertSame(0, ServiceCommission::query()->count());
    }

    public function test_commission_sync_is_idempotent(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 50,
            'is_active' => true,
        ]);

        $request = $this->completedRequest();

        $this->commissions()->syncFor($request);
        $this->commissions()->syncFor($request);

        $this->assertSame(1, ServiceCommission::query()->count());
    }

    public function test_commission_can_be_settled(): void
    {
        $commission = ServiceCommission::factory()->create();

        $result = $this->commissions()->settle($commission);

        $this->assertSame(CommissionStatus::Settled, $result->status);
        $this->assertNotNull($result->settled_at);
    }

    public function test_commission_can_be_waived(): void
    {
        $commission = ServiceCommission::factory()->settled()->create();

        $result = $this->commissions()->waive($commission);

        $this->assertSame(CommissionStatus::Waived, $result->status);
        $this->assertNull($result->settled_at);
    }

    public function test_completing_a_request_creates_the_commission_for_the_provider(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Percentage,
            'percentage_rate' => 8,
            'min_amount' => 0,
            'is_active' => true,
        ]);

        $providerUser = User::factory()->create();
        $provider = ServiceProvider::factory()->create(['user_id' => $providerUser->id]);
        $request = ServiceRequest::factory()->inProgress()->create([
            'service_provider_id' => $provider->id,
            'quoted_price' => 500,
        ]);

        app(ServiceRequestWorkflow::class)->complete($request, $providerUser);

        $commission = ServiceCommission::query()->firstOrFail();

        $this->assertSame($request->id, $commission->service_request_id);
        $this->assertSame($provider->id, $commission->service_provider_id);
        $this->assertEquals(40, (float) $commission->amount);
        $this->assertSame(CommissionStatus::Pending, $commission->status);
    }

    public function test_current_setting_prefers_the_active_database_row(): void
    {
        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Fixed,
            'fixed_amount' => 10,
            'is_active' => false,
        ]);

        CommissionSetting::factory()->create([
            'commission_type' => CommissionType::Percentage,
            'percentage_rate' => 20,
            'is_active' => true,
        ]);

        $setting = CommissionSetting::current();

        $this->assertTrue($setting->exists);
        $this->assertSame(CommissionType::Percentage, $setting->commission_type);
        $this->assertEquals(20, (float) $setting->percentage_rate);
    }
}
