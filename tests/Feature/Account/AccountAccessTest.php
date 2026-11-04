<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\BrokerDetails;
use App\Livewire\Account\CompanyDetails;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private const PAGES = [
        'account.dashboard',
        'account.profile',
        'account.security',
        'account.company',
        'account.broker',
    ];

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (self::PAGES as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    public function test_regular_user_can_open_dashboard_profile_and_security(): void
    {
        $user = User::factory()->create();

        foreach (['account.dashboard', 'account.profile', 'account.security'] as $name) {
            $this->actingAs($user)->get(route($name))->assertOk();
        }
    }

    public function test_regular_user_cannot_open_professional_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.company'))->assertForbidden();
        $this->actingAs($user)->get(route('account.broker'))->assertForbidden();
    }

    public function test_company_and_broker_pages_are_isolated_by_account_type(): void
    {
        $company = User::factory()->company()->create();
        $broker = User::factory()->broker()->create();

        $this->actingAs($company)->get(route('account.company'))->assertOk();
        $this->actingAs($company)->get(route('account.broker'))->assertForbidden();

        $this->actingAs($broker)->get(route('account.broker'))->assertOk();
        $this->actingAs($broker)->get(route('account.company'))->assertForbidden();
    }

    public function test_suspended_user_is_signed_out_and_redirected(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_account_pages_never_expose_another_user_data(): void
    {
        $user = User::factory()->create(['name' => 'صاحب الحساب الأول']);
        $other = User::factory()->create(['name' => 'مستخدم آخر مختلف']);

        foreach (['account.dashboard', 'account.profile', 'account.security'] as $name) {
            $this->actingAs($user)
                ->get(route($name))
                ->assertOk()
                ->assertSee('صاحب الحساب الأول')
                ->assertDontSee('مستخدم آخر مختلف')
                ->assertDontSee($other->email);
        }
    }

    public function test_professional_pages_never_expose_another_profile(): void
    {
        $company = User::factory()->company()->create();
        $company->companyProfile->update(['company_name' => 'شركة صاحب الحساب']);
        $otherCompany = User::factory()->company()->create();
        $otherCompany->companyProfile->update(['company_name' => 'شركة أخرى مختلفة']);

        $this->actingAs($company)
            ->get(route('account.company'))
            ->assertOk()
            ->assertDontSee('شركة أخرى مختلفة');

        Livewire::test(CompanyDetails::class)
            ->assertSet('company_name', 'شركة صاحب الحساب');

        $broker = User::factory()->broker()->create();
        $broker->brokerProfile->update(['professional_title' => 'وسيط صاحب الحساب']);
        $otherBroker = User::factory()->broker()->create();
        $otherBroker->brokerProfile->update(['professional_title' => 'وسيط آخر مختلف']);

        $this->actingAs($broker)
            ->get(route('account.broker'))
            ->assertOk()
            ->assertDontSee('وسيط آخر مختلف');

        Livewire::test(BrokerDetails::class)
            ->assertSet('professional_title', 'وسيط صاحب الحساب');
    }

    public function test_account_urls_do_not_accept_user_identifiers(): void
    {
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();

            if (! str_starts_with($name, 'account.')) {
                continue;
            }

            $checked++;

            $parameters = $route->parameterNames();

            // The property edit route accepts only the property primary key;
            // it never accepts a user identifier and ownership is enforced by
            // PropertyPolicy inside the component.
            if ($name === 'account.properties.edit') {
                $this->assertSame(['property'], $parameters);

                continue;
            }

            $this->assertSame([], $parameters, "Route {$name} must not accept identifiers.");
        }

        $this->assertSame(8, $checked);
    }
}
