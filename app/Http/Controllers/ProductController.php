<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\BookingItem;
use App\Models\ProductLike;
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
    //
    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('myflaver')) {
                $userId = Auth::id();
                $likedProductIds = ProductLike::where('user_id', $userId)->where('like', true)->pluck('product_id')->toArray();

                $dislikedProductIds = ProductLike::where('user_id', $userId)->where('like', false)->pluck('product_id')->toArray();

                $likedProducts = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->whereIn('id', $likedProductIds)
                    ->get();

                $excludedProductIds = array_merge($likedProductIds, $dislikedProductIds);

                $otherProducts = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->whereNotIn('id', $excludedProductIds)
                    ->get();

                $products = $likedProducts->merge($otherProducts)->values();

                return response()->json(ProductResource::collection($products), Response::HTTP_OK);
            }

/*
 * GET /api/products?recommended=true
 *
 * Order:
 * 1. Products that Auth user bought before
 * 2. Products that other users bought before
 * 3. All other products
 */
            if ($request->has('recommended')) {
                $userId = Auth::id();

                /*
                 * 1. Get all product ids from booking_items
                 * where the booking belongs to the authenticated user.
                 */
                $authUserProductIds = BookingItem::whereHas('booking', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('is_deleted', false);
                })
                    ->pluck('product_id')
                    ->unique()
                    ->values()
                    ->toArray();

                /*
                 * 2. Get all product ids from booking_items
                 * where the booking belongs to other users.
                 *
                 * Important:
                 * Exclude products that already exist in $authUserProductIds,
                 * because they are already in the first section.
                 */
                $otherUsersProductIds = BookingItem::whereHas('booking', function ($query) use ($userId) {
                    $query->where('user_id', '!=', $userId)->where('is_deleted', false);
                })
                    ->whereNotIn('product_id', $authUserProductIds)
                    ->pluck('product_id')
                    ->unique()
                    ->values()
                    ->toArray();

                /*
                 * 3. First query:
                 * Products that the authenticated user bought.
                 */
                $authUserProducts = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->whereIn('id', $authUserProductIds)
                    ->get();

                /*
                 * 4. Second query:
                 * Products that other users bought.
                 */
                $otherUsersProducts = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->whereIn('id', $otherUsersProductIds)
                    ->get();

                /*
                 * 5. Exclude both:
                 * - Auth user products
                 * - Other users products
                 */
                $excludedProductIds = array_merge($authUserProductIds, $otherUsersProductIds);

                /*
                 * 6. Third query:
                 * All other products that were not bought by auth user
                 * and not bought by other users.
                 */
                $otherProducts = Product::with(['image'])
                    ->where('is_deleted', false)
                    ->whereNotIn('id', $excludedProductIds)
                    ->get();

                /*
                 * 7. Merge final result:
                 * Auth user bought products first,
                 * then products bought by other users,
                 * then all other products.
                 */
                $products = $authUserProducts->merge($otherUsersProducts)->merge($otherProducts)->values();

                return response()->json(ProductResource::collection($products), Response::HTTP_OK);
            }

            /*
             * GET /api/products
             * Regular products list.
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
     * POST /api/products/like
     * Like or dislike product based on authenticated user.
     */
    public function likeOrDislike(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'like' => ['nullable', 'boolean'],
            ]);

            $userId = Auth::id();

            $productLike = ProductLike::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $validated['product_id'],
                ],
                [
                    'like' => $validated['like'] ?? true,
                ],
            );

            return response()->json(
                [
                    'message' => $validated['like'] ? 'Product liked successfully' : 'Product disliked successfully',
                    'data' => $productLike,
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Product likeOrDislike error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Something went wrong',
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
