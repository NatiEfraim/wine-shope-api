<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Image;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {
            // Recommendation Engine: Weighted Scoring & Collaborative Filtering
            if ($request->has('recommended')) {
                $userId = Auth::id();

                // Get all active products
                $products = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->where('is_active', true)
                    ->get();

                if ($userId) {
                    // 1. User's Purchase History (Frequency & Recency)
                    $userBookings = Booking::with('items')
                        ->where('user_id', $userId)
                        ->where('is_deleted', false)
                        ->get();

                    $userPurchasedProductIds = [];
                    $userProductScores = [];

                    foreach ($userBookings as $booking) {
                        // Recency Multiplier: Decays over 365 days. 
                        // Today = 2.0x weight, 1 year ago = 1.0x weight.
                        $daysAgo = max(0, now()->diffInDays($booking->created_at));
                        $recencyMultiplier = max(1.0, 2.0 - ($daysAgo / 365));

                        foreach ($booking->items as $item) {
                            $pid = $item->product_id;
                            $userPurchasedProductIds[$pid] = true;

                            if (!isset($userProductScores[$pid])) {
                                $userProductScores[$pid] = 0;
                            }

                            // Score = Base points (10) * Quantity * Recency
                            $userProductScores[$pid] += ($item->quantity * 10) * $recencyMultiplier;
                        }
                    }

                    $userPurchasedProductIds = array_keys($userPurchasedProductIds);

                    // 2. Collaborative Filtering (Similar Users)
                    $similarUserScores = [];
                    if (!empty($userPurchasedProductIds)) {
                        // Find bookings of other users who bought ANY of the products this user bought
                        $similarUsersBookings = Booking::with('items')
                            ->where('user_id', '!=', $userId)
                            ->where('is_deleted', false)
                            ->whereHas('items', function ($query) use ($userPurchasedProductIds) {
                                $query->whereIn('product_id', $userPurchasedProductIds);
                            })
                            ->get();

                        foreach ($similarUsersBookings as $booking) {
                            foreach ($booking->items as $item) {
                                $pid = $item->product_id;
                                if (!isset($similarUserScores[$pid])) {
                                    $similarUserScores[$pid] = 0;
                                }
                                // Add points based on what similar users bought (weight 3)
                                $similarUserScores[$pid] += ($item->quantity * 3);
                            }
                        }
                    }

                    // 3. Global Popularity (Fallback & Baseline)
                    $globalSales = BookingItem::whereHas('booking', function ($query) {
                            $query->where('is_deleted', false);
                        })
                        ->selectRaw('product_id, SUM(quantity) as total_qty')
                        ->groupBy('product_id')
                        ->pluck('total_qty', 'product_id')
                        ->toArray();

                    // 4. Calculate Final Scores & Map to Products
                    $scoredProducts = $products->map(function ($product) use ($userProductScores, $similarUserScores, $globalSales) {
                        $pid = $product->id;
                        $score = 0;

                        $score += $userProductScores[$pid] ?? 0;
                        $score += $similarUserScores[$pid] ?? 0;
                        $score += ($globalSales[$pid] ?? 0) * 1; // 1 point per global sale

                        $product->recommendation_score = $score;
                        return $product;
                    });

                    // Sort by score descending
                    $products = $scoredProducts->sortByDesc('recommendation_score')->values();
                }

                return response()->json(ProductResource::collection($products), Response::HTTP_OK);
            }

            /*
             * Regular products list (No recommendation parameter)
             */
            $products = Product::with(['image'])
                ->where('is_deleted', false)
                ->get();

            return response()->json(ProductResource::collection($products), Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Product index error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Failed to fetch products',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * GET /api/products/{id}
     */
    public function show($id)
    {
        try {
            $product = Product::with(['image'])
                ->where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$product) {
                return response()->json(
                    [
                        'message' => 'Product not found',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            return response()->json(new ProductResource($product), Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Product show error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Failed to fetch product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * POST /api/products
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            if ($request->hasFile('image')) {
                $uploadedImage = app(StorageController::class)->uploadImageToBucket($request->file('image'));

                if (!is_array($uploadedImage) || ($uploadedImage['status'] ?? null) !== Response::HTTP_OK) {
                    DB::rollBack();

                    return response()->json(
                        [
                            'message' => 'Failed to upload product image',
                        ],
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                    );
                }

                $image = Image::create([
                    'name' => $uploadedImage['randomFileName'],
                    'type' => $uploadedImage['extension'],
                    'file_name' => $uploadedImage['originalName'],
                    'path' => $uploadedImage['imagePath'],
                ]);

                $validated['image_id'] = $image->id;
            }

            unset($validated['image']);

            $product = Product::create($validated);

            DB::commit();

            return response()->json(
                [
                    'message' => 'Product created successfully',
                    'data' => $product->load('image'),
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Throwable $e) {
            Log::error('Product store error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to create product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::where('is_deleted', false)->where('id', $id)->first();

            if (!$product) {
                return response()->json(
                    [
                        'message' => 'Product not found',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['sometimes', 'numeric', 'min:0'],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'quantity' => ['sometimes', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            $product->update($validated);

            return response()->json(
                [
                    'data' => $product,
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Product update error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Failed to update product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy($id)
    {
        try {
            $product = Product::where('is_deleted', false)->where('id', $id)->first();

            if (!$product) {
                return response()->json(
                    [
                        'message' => 'Product not found',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            $product->update([
                'is_deleted' => true,
            ]);

            return response()->json(
                [
                    'message' => 'Product deleted successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Product delete error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Failed to delete product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}