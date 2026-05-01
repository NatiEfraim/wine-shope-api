<?php

namespace Database\Seeders;

use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         $products = [
       [
                'name' => 'Cabernet Sauvignon',
                'description' => 'Rich red wine with blackcurrant flavors',
                'price' => 120,
                'discount' => 20, // 20%
                'quantity' => 50,
            ],
            [
                'name' => 'Merlot',
                'description' => 'Soft and fruity red wine',
                'price' => 95,
                'discount' => null,
                'quantity' => 30,
            ],
            [
                'name' => 'Chardonnay',
                'description' => 'Dry white wine with citrus notes',
                'price' => 110,
                'discount' => 15, // 15%
                'quantity' => 40,
            ],
            [
                'name' => 'Rosé Wine',
                'description' => 'Light and refreshing pink wine',
                'price' => 85,
                'discount' => 10, // 10%
                'quantity' => 25,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
