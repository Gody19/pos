<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $count = max(0, 100 - Customer::count());

        for ($i = 0; $i < $count; $i++) {
            Customer::create([
                'customer_code' => 'CUS-'.str_pad((string) (Customer::withTrashed()->max('id') + 1), 5, '0', STR_PAD_LEFT),
                'full_name' => fake()->name(),
                'phone' => fake()->unique()->numerify('+2557########'),
                'email' => fake()->unique()->safeEmail(),
                'address' => fake()->optional()->streetAddress().', '.fake()->city(),
                'loyalty_points' => fake()->numberBetween(0, 2500),
            ]);
        }
    }
}
