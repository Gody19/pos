<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends ApiTestCase
{
    public function test_cashier_can_login_and_receive_token(): void
    {
        $user = $this->createUser('cashier');

        $response = $this->postJson($this->apiBase().'/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role_name']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createUser('cashier');

        $response = $this->postJson($this->apiBase().'/login', [
            'email' => 'wrong@example.com',
            'password' => 'nope',
        ]);

        $response->assertStatus(401);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role_name' => 'cashier',
            'password' => Hash::make('password'),
            'status' => false,
        ]);

        $response = $this->postJson($this->apiBase().'/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->createUser('cashier');

        $response = $this->getJson($this->apiBase().'/me', $this->authHeaders($user));

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_protected_route_requires_authentication(): void
    {
        $response = $this->getJson($this->apiBase().'/me');

        $response->assertStatus(401);
    }

    public function test_logout_revokes_token(): void
    {
        $user = $this->createUser('cashier');

        $this->postJson($this->apiBase().'/logout', [], $this->authHeaders($user))->assertOk();

        $this->assertCount(0, $user->tokens);
    }
}
