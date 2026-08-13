<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'customer_code' => fake()->unique()->numerify('CUS-#####'),
            'full_name' => fake()->name(),
            'phone' => fake()->unique()->numerify('+2557########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->optional()->streetAddress().', '.fake()->city(),
            'loyalty_points' => fake()->numberBetween(0, 2500),
        ];
    }
}
