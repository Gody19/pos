<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'quantity' => fake()->numberBetween(0, 250),
            'reorder_level' => fake()->numberBetween(3, 20),
            'location' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'Store Room', 'Freezer']),
        ];
    }
}
