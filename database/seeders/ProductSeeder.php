<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disk = Storage::disk('minio');
        $bucket = config('filesystems.disks.minio.bucket');
        $folder = 'images';

        // 100% verified Unsplash URLs from the project's own frontend assets to prevent 404s
        $verifiedImages = [
            'red_1' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&q=85&w=900&h=1100',
            'red_2' => 'https://images.unsplash.com/photo-1584916201218-f4242ceb4809?auto=format&fit=crop&q=85&w=900&h=1100',
            'red_3' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?auto=format&fit=crop&q=85&w=900&h=1100',
            'red_4' => 'https://images.unsplash.com/photo-1474722886779-9ae983985799?auto=format&fit=crop&q=85&w=900&h=1100',
            'red_5' => 'https://images.unsplash.com/photo-1569529465846-df6f40bc0b48?auto=format&fit=crop&q=85&w=900&h=1100',
            'red_6' => 'https://images.unsplash.com/photo-1571613316888-6f45407139ca?auto=format&fit=crop&q=85&w=900&h=1100',
            'white_1' => 'https://images.unsplash.com/photo-1568213816046-0ee1c42bd559?auto=format&fit=crop&q=85&w=900&h=1100',
            'white_2' => 'https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?auto=format&fit=crop&q=85&w=900&h=1100',
            'white_3' => 'https://images.unsplash.com/photo-1629205606573-eb916d848e84?auto=format&fit=crop&q=85&w=900&h=1100',
            'white_4' => 'https://images.unsplash.com/photo-1547595628-c61a29f496e0?auto=format&fit=crop&q=85&w=900&h=1100',
            'rose_1' => 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?auto=format&fit=crop&q=85&w=900&h=1100',
            'rose_2' => 'https://images.unsplash.com/photo-1553361371-8734ebada588?auto=format&fit=crop&q=85&w=900&h=1100',
            'sparkling' => 'https://images.unsplash.com/photo-1527281400648-7aae79c287bc?auto=format&fit=crop&q=85&w=900&h=1100',
        ];

        // 30 Products strictly mapped to verified image categories to avoid failures
        $products = [
            ['name' => 'Cabernet Sauvignon', 'description' => 'יין אדום עשיר עם טעמי פירות יער שחורים ודומדמניות', 'price' => 120, 'discount' => 20, 'quantity' => 50, 'source_url' => $verifiedImages['red_1']],
            ['name' => 'Merlot', 'description' => 'יין אדום רך ופירותי עם סיומת חלקה', 'price' => 95, 'discount' => null, 'quantity' => 4, 'source_url' => $verifiedImages['red_2']],
            ['name' => 'Chardonnay', 'description' => 'יין לבן יבש עם נגיעות הדרים ורעננות', 'price' => 110, 'discount' => 15, 'quantity' => 6, 'source_url' => $verifiedImages['white_1']],
            ['name' => 'Rosé Wine', 'description' => 'יין רוזה קליל ומרענן המתאים לקיץ', 'price' => 85, 'discount' => 10, 'quantity' => 25, 'source_url' => $verifiedImages['rose_1']],
            ['name' => 'Pinot Noir', 'description' => 'יין אדום אלגנטי עם ארומות דובדבן ופירות אדומים', 'price' => 130, 'discount' => 12, 'quantity' => 35, 'source_url' => $verifiedImages['red_3']],
            ['name' => 'Sauvignon Blanc', 'description' => 'יין לבן רענן עם טעמים טרופיים וחמיצות עדינה', 'price' => 90, 'discount' => null, 'quantity' => 45, 'source_url' => $verifiedImages['white_2']],
            ['name' => 'Shiraz', 'description' => 'יין אדום עוצמתי עם סיומת מתובלת', 'price' => 140, 'discount' => 18, 'quantity' => 28, 'source_url' => $verifiedImages['red_4']],
            ['name' => 'Malbec', 'description' => 'יין אדום עמוק עם טעמי שזיף וקקאו', 'price' => 115, 'discount' => 8, 'quantity' => 32, 'source_url' => $verifiedImages['red_5']],
            ['name' => 'Gewürztraminer', 'description' => 'יין לבן ארומטי עם ניחוחות פרחוניים', 'price' => 105, 'discount' => null, 'quantity' => 20, 'source_url' => $verifiedImages['white_3']],
            ['name' => 'Riesling', 'description' => 'יין לבן חצי יבש עם טעמי תפוח ירוק', 'price' => 100, 'discount' => 10, 'quantity' => 38, 'source_url' => $verifiedImages['white_4']],
            
            ['name' => 'Moscato', 'description' => 'יין לבן מתוק עם ארומות אפרסק ופירות קיץ', 'price' => 75, 'discount' => 5, 'quantity' => 60, 'source_url' => $verifiedImages['white_1']],
            ['name' => 'Zinfandel', 'description' => 'יין אדום פירותי עם טעמי פירות יער', 'price' => 125, 'discount' => null, 'quantity' => 8, 'source_url' => $verifiedImages['red_6']],
            ['name' => 'Syrah Reserve', 'description' => 'יין סירה פרימיום שעבר יישון בחביות עץ אלון', 'price' => 180, 'discount' => 20, 'quantity' => 15, 'source_url' => $verifiedImages['red_2']],
            ['name' => 'White Blend', 'description' => 'בלנד לבן מאוזן לשתייה יומיומית', 'price' => 70, 'discount' => null, 'quantity' => 55, 'source_url' => $verifiedImages['white_2']],
            ['name' => 'Red Blend', 'description' => 'בלנד אדום חלק ובעל גוף עשיר', 'price' => 88, 'discount' => 10, 'quantity' => 48, 'source_url' => $verifiedImages['red_1']],
            ['name' => 'Sparkling Brut', 'description' => 'יין מבעבע יבש המתאים לחגיגות ואירועים', 'price' => 160, 'discount' => 15, 'quantity' => 18, 'source_url' => $verifiedImages['sparkling']],
            ['name' => 'Prosecco', 'description' => 'יין מבעבע איטלקי עם סיומת פירותית', 'price' => 135, 'discount' => null, 'quantity' => 26, 'source_url' => $verifiedImages['sparkling']],
            ['name' => 'Port Wine', 'description' => 'יין מחוזק ומתוק בעל טעמים עמוקים', 'price' => 150, 'discount' => 12, 'quantity' => 17, 'source_url' => $verifiedImages['red_4']],
            ['name' => 'Dessert Wine', 'description' => 'יין קינוח מתוק עם נגיעות דבש', 'price' => 98, 'discount' => 7, 'quantity' => 33, 'source_url' => $verifiedImages['white_4']],
            ['name' => 'Petit Verdot', 'description' => 'יין אדום עוצמתי עם טעמי פירות כהים', 'price' => 145, 'discount' => null, 'quantity' => 21, 'source_url' => $verifiedImages['red_5']],
            
            ['name' => 'Viognier', 'description' => 'יין לבן מלא גוף עם ארומה פרחונית', 'price' => 118, 'discount' => 9, 'quantity' => 29, 'source_url' => $verifiedImages['white_3']],
            ['name' => 'Grenache', 'description' => 'יין אדום עם טעמי תות ותבלינים', 'price' => 108, 'discount' => 6, 'quantity' => 31, 'source_url' => $verifiedImages['red_3']],
            ['name' => 'Tempranillo', 'description' => 'יין אדום ספרדי עם נגיעות וניל ועץ אלון', 'price' => 122, 'discount' => 11, 'quantity' => 27, 'source_url' => $verifiedImages['red_6']],
            ['name' => 'Barbera', 'description' => 'יין אדום איטלקי עם חמיצות מאוזנת', 'price' => 112, 'discount' => null, 'quantity' => 36, 'source_url' => $verifiedImages['red_1']],
            ['name' => 'Chianti', 'description' => 'יין אדום איטלקי קלאסי בעל גוף בינוני', 'price' => 128, 'discount' => 13, 'quantity' => 24, 'source_url' => $verifiedImages['red_2']],
            ['name' => 'Cava Brut', 'description' => 'יין מבעבע ספרדי יבש ומרענן', 'price' => 102, 'discount' => 8, 'quantity' => 42, 'source_url' => $verifiedImages['sparkling']],
            ['name' => 'Ice Wine', 'description' => 'יין קינוח יוקרתי מענבים קפואים', 'price' => 220, 'discount' => 15, 'quantity' => 2, 'source_url' => $verifiedImages['white_4']],
            ['name' => 'Organic Red Wine', 'description' => 'יין אדום אורגני יבש ואיכותי', 'price' => 135, 'discount' => null, 'quantity' => 19, 'source_url' => $verifiedImages['red_5']],
            ['name' => 'Organic White Wine', 'description' => 'יין לבן אורגני יבש ומרענן', 'price' => 125, 'discount' => 10, 'quantity' => 23, 'source_url' => $verifiedImages['white_2']],
            ['name' => 'Premium Rosé', 'description' => 'יין רוזה פרימיום עם טעמי פירות יער טריים', 'price' => 155, 'discount' => 14, 'quantity' => 16, 'source_url' => $verifiedImages['rose_2']],
        ];

        foreach ($products as $productData) {
            $imageUrl = $productData['source_url'];
            
            // Create a deterministic, permanent filename based on the product name for 30 distinct files in MinIO
            $imageName = 'wine_' . Str::slug($productData['name']) . '.jpg';
            $path = $bucket . '/' . $folder . '/' . $imageName;
            $imageId = null;

            try {
                // Smart Check: Only download if it doesn't already exist in MinIO
                if (!$disk->exists($path)) {
                    // Suppress warnings on file_get_contents and handle gracefully
                    $contents = @file_get_contents($imageUrl);
                    if ($contents) {
                        $disk->put($path, $contents);
                    } else {
                        Log::warning("Failed to download image from Unsplash for {$productData['name']}");
                    }
                }

                // Create the Image record only if the physical file is confirmed in MinIO
                if ($disk->exists($path)) {
                    $image = Image::updateOrCreate(
                        ['file_name' => $imageName],
                        [
                            'name' => $imageName,
                            'type' => 'jpg',
                            'path' => $path
                        ]
                    );
                    
                    $imageId = $image->id;
                }
            } catch (\Exception $e) {
                Log::error("Failed to process image for {$productData['name']}: " . $e->getMessage());
            }

            // Remove the helper URL before saving to the database
            unset($productData['source_url']);
            
            $productData['sku'] = strtoupper(Str::random(8));
            $productData['image_id'] = $imageId;

            Product::create($productData);
        }
    }
}