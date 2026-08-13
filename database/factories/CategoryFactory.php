<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Soft Drinks', 'Beverages', 'Snacks', 'Bakery', 'Dairy', 'Fruits', 'Vegetables',
                'Meat & Poultry', 'Seafood', 'Frozen Foods', 'Canned Goods', 'Pasta & Rice',
                'Cereals', 'Cooking Oil', 'Spices & Seasonings', 'Sauces & Condiments', 'Candy & Sweets',
                'Ice Cream', 'Coffee & Tea', 'Juices', 'Bottled Water', 'Personal Care', 'Household Cleaners',
                'Laundry', 'Baby Care', 'Pet Supplies', 'Stationery', 'Electronics', 'Phone Accessories',
                'Chargers & Cables', 'Batteries', 'Light Bulbs', 'Hardware', 'Plasticware', 'Paper Products',
                'Cleaning Tools', 'Air Fresheners', 'Insect Repellents', 'Toiletries', 'First Aid',
                'Pharmacy', 'Vitamins & Supplements', 'Sports & Fitness', 'Toys', 'Books & Magazines',
                'Cosmetics', 'Hair Care', 'Oral Care', 'Skin Care', 'Kitchen Appliances',
            ]),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->sentence(),
            'status' => true,
        ];
    }

    public function withProducts(int $count = 10): static
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            Product::factory()->count($count)->for($category)->create();
        });
    }
}
