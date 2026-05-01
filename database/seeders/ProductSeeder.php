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
            ['name' => 'Cabernet Sauvignon', 'description' => 'Rich red wine with blackcurrant flavors', 'price' => 120, 'discount' => 20, 'quantity' => 50],
            ['name' => 'Merlot', 'description' => 'Soft and fruity red wine', 'price' => 95, 'discount' => null, 'quantity' => 30],
            ['name' => 'Chardonnay', 'description' => 'Dry white wine with citrus notes', 'price' => 110, 'discount' => 15, 'quantity' => 40],
            ['name' => 'Rosé Wine', 'description' => 'Light and refreshing pink wine', 'price' => 85, 'discount' => 10, 'quantity' => 25],
            ['name' => 'Pinot Noir', 'description' => 'Elegant red wine with cherry aromas', 'price' => 130, 'discount' => 12, 'quantity' => 35],
            ['name' => 'Sauvignon Blanc', 'description' => 'Fresh white wine with tropical fruit notes', 'price' => 90, 'discount' => null, 'quantity' => 45],
            ['name' => 'Shiraz', 'description' => 'Bold red wine with spicy finish', 'price' => 140, 'discount' => 18, 'quantity' => 28],
            ['name' => 'Malbec', 'description' => 'Deep red wine with plum and cocoa flavors', 'price' => 115, 'discount' => 8, 'quantity' => 32],
            ['name' => 'Gewürztraminer', 'description' => 'Aromatic white wine with floral notes', 'price' => 105, 'discount' => null, 'quantity' => 20],
            ['name' => 'Riesling', 'description' => 'Semi-dry white wine with apple notes', 'price' => 100, 'discount' => 10, 'quantity' => 38],

            ['name' => 'Moscato', 'description' => 'Sweet white wine with peach aromas', 'price' => 75, 'discount' => 5, 'quantity' => 60],
            ['name' => 'Zinfandel', 'description' => 'Fruity red wine with berry taste', 'price' => 125, 'discount' => null, 'quantity' => 22],
            ['name' => 'Syrah Reserve', 'description' => 'Premium Syrah with oak aging', 'price' => 180, 'discount' => 20, 'quantity' => 15],
            ['name' => 'White Blend', 'description' => 'Balanced white blend for daily drinking', 'price' => 70, 'discount' => null, 'quantity' => 55],
            ['name' => 'Red Blend', 'description' => 'Smooth red blend with rich body', 'price' => 88, 'discount' => 10, 'quantity' => 48],
            ['name' => 'Sparkling Brut', 'description' => 'Dry sparkling wine for celebrations', 'price' => 160, 'discount' => 15, 'quantity' => 18],
            ['name' => 'Prosecco', 'description' => 'Italian sparkling wine with fruity finish', 'price' => 135, 'discount' => null, 'quantity' => 26],
            ['name' => 'Port Wine', 'description' => 'Sweet fortified wine', 'price' => 150, 'discount' => 12, 'quantity' => 17],
            ['name' => 'Dessert Wine', 'description' => 'Sweet dessert wine with honey notes', 'price' => 98, 'discount' => 7, 'quantity' => 33],
            ['name' => 'Petit Verdot', 'description' => 'Powerful red wine with dark fruit flavors', 'price' => 145, 'discount' => null, 'quantity' => 21],

            ['name' => 'Viognier', 'description' => 'Full-bodied white wine with floral aroma', 'price' => 118, 'discount' => 9, 'quantity' => 29],
            ['name' => 'Grenache', 'description' => 'Red wine with strawberry and spice notes', 'price' => 108, 'discount' => 6, 'quantity' => 31],
            ['name' => 'Tempranillo', 'description' => 'Spanish red wine with oak and vanilla notes', 'price' => 122, 'discount' => 11, 'quantity' => 27],
            ['name' => 'Barbera', 'description' => 'Italian red wine with bright acidity', 'price' => 112, 'discount' => null, 'quantity' => 36],
            ['name' => 'Chianti', 'description' => 'Classic Italian red wine', 'price' => 128, 'discount' => 13, 'quantity' => 24],
            ['name' => 'Cava Brut', 'description' => 'Spanish sparkling wine', 'price' => 102, 'discount' => 8, 'quantity' => 42],
            ['name' => 'Ice Wine', 'description' => 'Sweet premium wine made from frozen grapes', 'price' => 220, 'discount' => 15, 'quantity' => 10],
            ['name' => 'Organic Red Wine', 'description' => 'Organic dry red wine', 'price' => 135, 'discount' => null, 'quantity' => 19],
            ['name' => 'Organic White Wine', 'description' => 'Organic dry white wine', 'price' => 125, 'discount' => 10, 'quantity' => 23],
            ['name' => 'Premium Rosé', 'description' => 'Premium rosé wine with fresh berry notes', 'price' => 155, 'discount' => 14, 'quantity' => 16],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
