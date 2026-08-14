<?php

namespace Tests\Feature;

use App\Models\User;

class LoginTest extends WebTestCase
{
    public function test_login_page_loads(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in');
    }

    public function test_cashier_can_log_in_via_web(): void
    {
        $user = $this->createUser('cashier');

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createUser('cashier');

        $this->post(route('login.attempt'), [
            'email' => 'wrong@example.com',
            'password' => 'nope',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = $this->createUser('cashier', ['status' => false]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_logs_user_out(): void
    {
        $user = $this->createUser('cashier');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}