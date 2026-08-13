<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 200000);
        $discount = fake()->randomElement([0, 0, 0, 0, fake()->numberBetween(100, 5000)]);
        $tax = round($subtotal * fake()->randomElement([0, 0.05, 0.18]), 2);

        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######-####'),
            'user_id' => User::factory(),
            'shift_id' => Shift::factory(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => round($subtotal - $discount + $tax, 2),
            'amount_paid' => $subtotal - $discount + $tax,
            'change_due' => 0,
            'payment_status' => 'completed',
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile', 'qr']),
            'notes' => null,
            'sold_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
