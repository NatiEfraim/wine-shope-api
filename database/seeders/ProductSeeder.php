<?php

namespace Database\Seeders;

use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Cabernet Sauvignon', 'description' => 'יין אדום עשיר עם טעמי פירות יער שחורים ודומדמניות', 'price' => 120, 'discount' => 20, 'quantity' => 50],
            ['name' => 'Merlot', 'description' => 'יין אדום רך ופירותי עם סיומת חלקה', 'price' => 95, 'discount' => null, 'quantity' => 30],
            ['name' => 'Chardonnay', 'description' => 'יין לבן יבש עם נגיעות הדרים ורעננות', 'price' => 110, 'discount' => 15, 'quantity' => 4],
            ['name' => 'Rosé Wine', 'description' => 'יין רוזה קליל ומרענן המתאים לקיץ', 'price' => 85, 'discount' => 10, 'quantity' => 25],
            ['name' => 'Pinot Noir', 'description' => 'יין אדום אלגנטי עם ארומות דובדבן ופירות אדומים', 'price' => 130, 'discount' => 12, 'quantity' => 35],
            ['name' => 'Sauvignon Blanc', 'description' => 'יין לבן רענן עם טעמים טרופיים וחמיצות עדינה', 'price' => 90, 'discount' => null, 'quantity' => 8],
            ['name' => 'Shiraz', 'description' => 'יין אדום עוצמתי עם סיומת מתובלת', 'price' => 140, 'discount' => 18, 'quantity' => 28],
            ['name' => 'Malbec', 'description' => 'יין אדום עמוק עם טעמי שזיף וקקאו', 'price' => 115, 'discount' => 8, 'quantity' => 32],
            ['name' => 'Gewürztraminer', 'description' => 'יין לבן ארומטי עם ניחוחות פרחוניים', 'price' => 105, 'discount' => null, 'quantity' => 20],
            ['name' => 'Riesling', 'description' => 'יין לבן חצי יבש עם טעמי תפוח ירוק', 'price' => 100, 'discount' => 10, 'quantity' => 38],

            ['name' => 'Moscato', 'description' => 'יין לבן מתוק עם ארומות אפרסק ופירות קיץ', 'price' => 75, 'discount' => 5, 'quantity' => 60],
            ['name' => 'Zinfandel', 'description' => 'יין אדום פירותי עם טעמי פירות יער', 'price' => 125, 'discount' => null, 'quantity' => 22],
            ['name' => 'Syrah Reserve', 'description' => 'יין סירה פרימיום שעבר יישון בחביות עץ אלון', 'price' => 180, 'discount' => 20, 'quantity' => 15],
            ['name' => 'White Blend', 'description' => 'בלנד לבן מאוזן לשתייה יומיומית', 'price' => 70, 'discount' => null, 'quantity' => 55],
            ['name' => 'Red Blend', 'description' => 'בלנד אדום חלק ובעל גוף עשיר', 'price' => 88, 'discount' => 10, 'quantity' => 48],
            ['name' => 'Sparkling Brut', 'description' => 'יין מבעבע יבש המתאים לחגיגות ואירועים', 'price' => 160, 'discount' => 15, 'quantity' => 18],
            ['name' => 'Prosecco', 'description' => 'יין מבעבע איטלקי עם סיומת פירותית', 'price' => 135, 'discount' => null, 'quantity' => 26],
            ['name' => 'Port Wine', 'description' => 'יין מחוזק ומתוק בעל טעמים עמוקים', 'price' => 150, 'discount' => 12, 'quantity' => 17],
            ['name' => 'Dessert Wine', 'description' => 'יין קינוח מתוק עם נגיעות דבש', 'price' => 98, 'discount' => 7, 'quantity' => 33],
            ['name' => 'Petit Verdot', 'description' => 'יין אדום עוצמתי עם טעמי פירות כהים', 'price' => 145, 'discount' => null, 'quantity' => 5],

            ['name' => 'Viognier', 'description' => 'יין לבן מלא גוף עם ארומה פרחונית', 'price' => 118, 'discount' => 9, 'quantity' => 29],
            ['name' => 'Grenache', 'description' => 'יין אדום עם טעמי תות ותבלינים', 'price' => 108, 'discount' => 6, 'quantity' => 31],
            ['name' => 'Tempranillo', 'description' => 'יין אדום ספרדי עם נגיעות וניל ועץ אלון', 'price' => 122, 'discount' => 11, 'quantity' => 27],
            ['name' => 'Barbera', 'description' => 'יין אדום איטלקי עם חמיצות מאוזנת', 'price' => 112, 'discount' => null, 'quantity' => 36],
            ['name' => 'Chianti', 'description' => 'יין אדום איטלקי קלאסי בעל גוף בינוני', 'price' => 128, 'discount' => 13, 'quantity' => 24],
            ['name' => 'Cava Brut', 'description' => 'יין מבעבע ספרדי יבש ומרענן', 'price' => 102, 'discount' => 8, 'quantity' => 42],
            ['name' => 'Ice Wine', 'description' => 'יין קינוח יוקרתי מענבים קפואים', 'price' => 220, 'discount' => 15, 'quantity' => 2],
            ['name' => 'Organic Red Wine', 'description' => 'יין אדום אורגני יבש ואיכותי', 'price' => 135, 'discount' => null, 'quantity' => 19],
            ['name' => 'Organic White Wine', 'description' => 'יין לבן אורגני יבש ומרענן', 'price' => 125, 'discount' => 10, 'quantity' => 23],
            ['name' => 'Premium Rosé', 'description' => 'יין רוזה פרימיום עם טעמי פירות יער טריים', 'price' => 155, 'discount' => 14, 'quantity' => 16],
        ];

        foreach ($products as $product) {
           $product['sku'] = strtoupper( Str::random(8));
           Product::create($product);
        }
    }
}