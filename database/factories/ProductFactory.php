<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->numberBetween(500, 150000);

        return [
            'barcode' => fake()->unique()->numerify('6###########'),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'name' => fake()->unique()->randomElement([
                'Fanta Orange', 'Coca Cola', 'Sprite', 'Pepsi', 'Mountain Dew', 'Mirinda',
                'Coca-Cola Zero', 'Fanta Pineapple', 'Novida Tropical', 'Kilimanjaro Water',
                'Afya Water', 'Crystal Water', 'Azam Energy Drink', 'Red Bull', 'Monster Energy',
                'Dr Pepper', '7Up', 'Schweppes Tonic', 'Schweppes Ginger Ale', 'Tango Apple',
                'Nescafe Classic', 'Africafe Instant', 'Azam Coffee', 'Dilmah Tea', 'Lipton Yellow Label',
                'Bongo Chocolate', 'Konyagi Gin', 'Villa Queen', 'Kilimanjaro Beer', 'Serengeti Lager',
                'Castle Milk Stout', 'Tusker', 'Safari Lager', 'Pilsner', 'Balimi',
                'Nivea Cream', 'Vaseline', 'Dettol Soap', 'Lifebuoy', 'Rexona', 'Close-Up Toothpaste',
                'Colgate', 'Oral-B Toothbrush', 'Gillette Razor', 'Head & Shoulders', 'Pantene',
                'Dove Shampoo', 'Sunlight Detergent', 'Omo Powder', 'Jik Bleach', 'Mr Muscle',
                'Dettol Disinfectant', 'Vim Dishwash', 'HarPic Toilet Cleaner', 'Glade Air Freshener',
                'Raid Insect Killer', 'Baygon', 'Uzuri Biscuits', 'Nuvita Biscuits', 'Parle G',
                'Tango Wafers', 'Bismark Biscuits', 'Lay’s Chips', 'Pringles', 'Doritos',
                'Bingo Chips', 'Azam Popcorn', 'Peanut Crunch', 'Cadbury Dairy Milk', 'Milka Chocolate',
                'Twix', 'Mars', 'Snickers', 'KitKat', 'Skittles', 'M&M’s', 'Haribo Gummy Bears',
                'Big Babol Gum', 'Lifesavers', 'Tic Tac', 'Blue Band Margarine', 'Kimbo Cooking Oil',
                'Sunflower Oil', 'Azam Rice', 'Basmati Rice', 'Tusky Flour', 'Sembe Maize Flour',
                'Bakers Flour', 'Unga Wheat', 'Pasta Spaghetti', 'Maccaroni', 'Instant Noodles',
                'Tomato Paste', 'Ketchup', 'Mayonnaise', 'Maggi Cubes', 'Royco', 'Soy Sauce',
                'Chili Sauce', 'Vinegar', 'Salt', 'Sugar', 'Azam Sugar', 'Baking Powder',
                'Norbrook Biscuits', 'Oreo', 'Nutella', 'Peanut Butter', 'Honey',
                'Chum Saiz', 'Mama’s Pride', 'Salted Gems', 'Ginger Biscuits',
            ]),
            'description' => fake()->optional()->sentence(),
            'cost_price' => round($price * 0.7, 2),
            'selling_price' => $price,
            'tax_rate' => fake()->randomElement([0, 0, 5, 5, 10, 18]),
            'image' => null,
            'status' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            Inventory::factory()->for($product)->create();
        });
    }
}
