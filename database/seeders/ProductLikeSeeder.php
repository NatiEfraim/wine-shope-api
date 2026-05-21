<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductLike;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
                /*
         * Get users from database.
         * You can change the number as you want.
         */
        $users = User::query()
            ->get();

        /*
         * Get products from database.
         * You can change the number as you want.
         */
        $products = Product::query()
            ->where('is_deleted', false)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(20)
            ->get();



        /*
         * For each user, like random products.
         */
        foreach ($users as $user) {
            $randomProducts = $products->random(
                min(rand(1, 5), $products->count())
            );

            foreach ($randomProducts as $product) {
                ProductLike::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'like' => true,
                    ]
                );
            }
        }

    }
}
