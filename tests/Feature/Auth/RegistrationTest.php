<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'phone' => '01000000000',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'account_type' => AccountType::User->value,
            'terms' => true,
        ], $overrides);
    }

    public function test_guests_can_view_the_registration_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('إنشاء حساب جديد')
            ->assertSee('نوع الحساب');
    }

    public function test_authenticated_users_are_redirected_away_from_registration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('home'));
    }

    public function test_regular_user_registration_creates_an_active_verified_account(): void
    {
        Livewire::test(Register::class)
            ->set($this->payload())
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(AccountType::User, $user->account_type);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isVerifiedAccount());
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertNotSame('Password123', $user->password);
        $this->assertNull($user->brokerProfile);
        $this->assertNull($user->companyProfile);
    }

    public function test_broker_registration_starts_pending_with_professional_profile(): void
    {
        Livewire::test(Register::class)
            ->set($this->payload([
                'email' => 'broker@example.com',
                'account_type' => AccountType::Broker->value,
            ]))
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $broker = User::query()->where('email', 'broker@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($broker);
        $this->assertSame(AccountType::Broker, $broker->account_type);
        $this->assertSame(AccountStatus::Pending, $broker->status);
        $this->assertFalse($broker->isAdmin());
        $this->assertFalse($broker->isVerifiedAccount());
        $this->assertFalse(Gate::forUser($broker)->allows('publish-properties'));
        $this->assertNotNull($broker->brokerProfile);
        $this->assertSame(VerificationStatus::Pending, $broker->brokerProfile->verification_status);
    }

    public function test_company_registration_requires_and_stores_company_name(): void
    {
        Livewire::test(Register::class)
            ->set($this->payload([
                'email' => 'company@example.com',
                'account_type' => AccountType::Company->value,
                'company_name' => 'شركة الإمداد للعقارات',
            ]))
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $company = User::query()->where('email', 'company@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($company);
        $this->assertSame(AccountType::Company, $company->account_type);
        $this->assertSame(AccountStatus::Pending, $company->status);
        $this->assertFalse($company->isAdmin());
        $this->assertFalse(Gate::forUser($company)->allows('publish-properties'));
        $this->assertNotNull($company->companyProfile);
        $this->assertSame('شركة الإمداد للعقارات', $company->companyProfile->company_name);
        $this->assertSame(VerificationStatus::Pending, $company->companyProfile->verification_status);
    }

    public function test_company_registration_without_company_name_fails(): void
    {
        Livewire::test(Register::class)
            ->set($this->payload([
                'email' => 'company@example.com',
                'account_type' => AccountType::Company->value,
                'company_name' => '',
            ]))
            ->call('register')
            ->assertHasErrors(['company_name' => 'required']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'company@example.com']);
    }

    public function test_registration_cannot_escalate_to_admin_or_force_another_status(): void
    {
        $component = new Register;

        $this->assertFalse(property_exists($component, 'is_admin'));
        $this->assertFalse(property_exists($component, 'status'));

        Livewire::test(Register::class)
            ->set($this->payload([
                'email' => 'attacker@example.com',
                'account_type' => 'admin',
            ]))
            ->call('register')
            ->assertHasErrors(['account_type']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    }

    public function test_registration_validates_unique_email_and_phone(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
            'phone' => '01111111111',
        ]);

        Livewire::test(Register::class)
            ->set($this->payload([
                'email' => 'taken@example.com',
                'phone' => '01111111111',
            ]))
            ->call('register')
            ->assertHasErrors(['email', 'phone']);
    }

    public function test_registration_rejects_weak_passwords_and_missing_terms(): void
    {
        Livewire::test(Register::class)
            ->set($this->payload([
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'terms' => false,
            ]))
            ->call('register')
            ->assertHasErrors(['password', 'terms']);

        $this->assertGuest();
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 20; $attempt++) {
            Livewire::test(Register::class)
                ->set($this->payload([
                    'email' => 'rate-'.$attempt.'@example.com',
                ]))
                ->call('register');
        }

        Livewire::test(Register::class)
            ->set($this->payload(['email' => 'blocked@example.com']))
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }
}
