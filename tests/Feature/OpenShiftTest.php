<?php

namespace Tests\Feature;

use App\Models\Shift;

class OpenShiftTest extends ApiTestCase
{
    public function test_cashier_can_open_shift(): void
    {
        $user = $this->createUser('cashier');

        $response = $this->postJson($this->apiBase().'/shifts/open', [
            'opening_balance' => 50000,
            'notes' => 'Morning shift',
        ], $this->authHeaders($user));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.opening_balance', 50000);

        $this->assertDatabaseHas('shifts', [
            'user_id' => $user->id,
            'status' => 'open',
            'opening_balance' => 50000,
        ]);
    }

    public function test_cannot_open_second_shift_while_open(): void
    {
        $user = $this->createUser('cashier');

        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $response = $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 20000], $headers);

        $response->assertStatus(409);
    }

    public function test_shift_current_returns_open_shift(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $response = $this->getJson($this->apiBase().'/shifts/current', $headers);

        $response->assertOk()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_shift_history_lists_shifts(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        Shift::create([
            'user_id' => $user->id,
            'opening_balance' => 10000,
            'opened_at' => now()->subDay(),
            'closed_at' => now()->subDay()->addHours(8),
            'status' => 'closed',
        ]);

        $response = $this->getJson($this->apiBase().'/shifts/history', $headers);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
