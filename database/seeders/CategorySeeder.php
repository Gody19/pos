<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Soft Drinks', 'Beverages', 'Snacks', 'Bakery', 'Dairy', 'Fruits', 'Vegetables',
            'Meat & Poultry', 'Seafood', 'Frozen Foods', 'Canned Goods', 'Pasta & Rice',
            'Cereals', 'Cooking Oil', 'Spices & Seasonings', 'Sauces & Condiments', 'Candy & Sweets',
            'Ice Cream', 'Coffee & Tea', 'Juices', 'Bottled Water', 'Personal Care', 'Household Cleaners',
            'Laundry', 'Baby Care', 'Pet Supplies', 'Stationery', 'Electronics', 'Phone Accessories',
            'Chargers & Cables', 'Batteries', 'Light Bulbs', 'Hardware', 'Plasticware', 'Paper Products',
            'Cleaning Tools', 'Air Fresheners', 'Insect Repellents', 'Toiletries', 'First Aid',
            'Pharmacy', 'Vitamins & Supplements', 'Sports & Fitness', 'Toys', 'Books & Magazines',
            'Cosmetics', 'Hair Care', 'Oral Care', 'Skin Care', 'Kitchen Appliances',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['description' => fake()->optional()->sentence(), 'status' => true]
            );
        }
    }
}
