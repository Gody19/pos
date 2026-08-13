<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $count = max(0, 500 - Product::count());
        $names = $this->productNames();

        for ($i = 0; $i < $count; $i++) {
            $category = $categories->random();

            $price = fake()->numberBetween(500, 150000);

            $baseName = $names[$i % count($names)];
            $name = $i < count($names)
                ? $baseName
                : $baseName.' '.($i - count($names) + 2);

            $product = Product::create([
                'category_id' => $category->id,
                'barcode' => fake()->unique()->numerify('6###########'),
                'sku' => 'SKU-'.str_pad((string) (Product::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'name' => $name,
                'description' => fake()->optional()->sentence(),
                'cost_price' => round($price * 0.7, 2),
                'selling_price' => $price,
                'tax_rate' => fake()->randomElement([0, 0, 0, 5, 5, 10, 18]),
                'status' => true,
            ]);

            Inventory::create([
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(0, 250),
                'reorder_level' => fake()->numberBetween(3, 20),
                'location' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'Store Room', 'Freezer']),
            ]);
        }
    }

    protected function productNames(): array
    {
        return [
            'Fanta Orange', 'Coca Cola', 'Sprite', 'Pepsi', 'Mountain Dew', 'Mirinda', 'Novida Tropical',
            'Kilimanjaro Water', 'Afya Water', 'Azam Energy Drink', 'Red Bull', 'Monster Energy',
            'Dr Pepper', '7Up', 'Schweppes Tonic', 'Nescafe Classic', 'Africafe Instant', 'Azam Coffee',
            'Dilmah Tea', 'Lipton Yellow Label', 'Bongo Chocolate', 'Serengeti Lager', 'Castle Milk Stout',
            'Tusker', 'Safari Lager', 'Pilsner', 'Nivea Cream', 'Vaseline', 'Dettol Soap', 'Lifebuoy',
            'Rexona', 'Close-Up Toothpaste', 'Colgate', 'Oral-B Toothbrush', 'Gillette Razor',
            'Head & Shoulders', 'Pantene', 'Dove Shampoo', 'Sunlight Detergent', 'Omo Powder', 'Jik Bleach',
            'Mr Muscle', 'Vim Dishwash', 'HarPic Toilet Cleaner', 'Glade Air Freshener', 'Raid Insect Killer',
            'Baygon', 'Uzuri Biscuits', 'Nuvita Biscuits', 'Parle G', 'Tango Wafers', 'Bismark Biscuits',
            'Lay’s Chips', 'Pringles', 'Doritos', 'Bingo Chips', 'Azam Popcorn', 'Cadbury Dairy Milk',
            'Milka Chocolate', 'Twix', 'Mars', 'Snickers', 'KitKat', 'Skittles', 'M&M’s',
            'Haribo Gummy Bears', 'Big Babol Gum', 'Lifesavers', 'Tic Tac', 'Blue Band Margarine',
            'Kimbo Cooking Oil', 'Sunflower Oil', 'Azam Rice', 'Basmati Rice', 'Tusky Flour',
            'Sembe Maize Flour', 'Bakers Flour', 'Pasta Spaghetti', 'Maccaroni', 'Instant Noodles',
            'Tomato Paste', 'Ketchup', 'Mayonnaise', 'Maggi Cubes', 'Royco', 'Soy Sauce', 'Chili Sauce',
            'Vinegar', 'Salt', 'Sugar', 'Azam Sugar', 'Baking Powder', 'Oreo', 'Nutella', 'Peanut Butter',
            'Honey', 'Chum Saiz', 'Mama’s Pride', 'Salted Gems', 'Ginger Biscuits',
        ];
    }
}
