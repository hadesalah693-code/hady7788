<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_log_out(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', __('app.auth.logged_out'));

        $this->assertGuest();
    }

    public function test_logout_requires_a_post_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('logout'))
            ->assertMethodNotAllowed();
    }

    public function test_logout_route_is_protected_by_session_and_csrf_middleware(): void
    {
        $middleware = app('router')->getRoutes()->getByName('logout')->gatherMiddleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('auth', $middleware);
    }
}
