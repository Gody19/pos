<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shift;

class ShiftFlowTest extends WebTestCase
{
    public function test_cashier_opens_shift_and_is_redirected_to_pos(): void
    {
        $user = $this->createUser('cashier');

        $this->actingAs($user)
            ->post(route('shifts.open.store'), ['opening_balance' => 15000])
            ->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('shifts', [
            'user_id' => $user->id,
            'opening_balance' => 15000,
            'status' => 'open',
        ]);
    }

    public function test_cashier_with_open_shift_can_view_pos(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);

        $this->actingAs($user)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Scan barcode')
            ->assertSee('Current Sale');
    }

    public function test_cashier_without_open_shift_is_redirected(): void
    {
        $user = $this->createUser('cashier');

        $this->actingAs($user)
            ->get(route('pos.index'))
            ->assertRedirect(route('shifts.open'));
    }

    public function test_cashier_cannot_open_two_shifts(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);

        $this->actingAs($user)
            ->post(route('shifts.open.store'), ['opening_balance' => 5000])
            ->assertRedirect(route('shifts.open'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('shifts', 1);
    }

    public function test_cashier_closes_shift_with_reconciliation(): void
    {
        $user = $this->createUser('cashier');
        $shift = $this->openShift($user, 20000);

        $this->actingAs($user)
            ->post(route('shifts.close.store'), ['closing_balance' => 45000])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'status' => 'closed',
            'closing_balance' => 45000,
        ]);
    }
}