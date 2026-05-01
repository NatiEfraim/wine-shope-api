<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
          $users = User::all();
        $products = Product::where('is_deleted', false)->get();
        $statuses = Status::all();

        if ($users->isEmpty() || $products->isEmpty() || $statuses->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            $user = $users->random();
            $status = $statuses->random();

            $booking = Booking::create([
                'serial_number' => rand(1000, 9999),
                'user_id' => $user->id,
                'status_id' => $status->id,
                'total_price' => 0,
                'is_deleted' => false,
            ]);

            $randomProducts = $products->random(rand(1, min(5, $products->count())));

            $bookingTotal = 0;

            foreach ($randomProducts as $product) {
                $quantity = rand(1, 4);

                $unitPrice = $product->price_after_discount;
                $totalPrice = $unitPrice * $quantity;

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                $bookingTotal += $totalPrice;
            }

            $booking->update([
                'total_price' => $bookingTotal,
            ]);
        }
    }
}
