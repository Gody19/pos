<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $closedAt = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'user_id' => User::factory(),
            'opening_balance' => fake()->numberBetween(0, 50000),
            'closing_balance' => fake()->numberBetween(50000, 800000),
            'opened_at' => $closedAt,
            'closed_at' => $closedAt,
            'status' => 'closed',
        ];
    }
}
