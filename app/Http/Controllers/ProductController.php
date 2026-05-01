<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    //
        /**
     * GET /api/products
     */
    public function index()
    {
        try {
            $products = Product::where('is_deleted', false)->get();

            return response()->json($products, Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Product index error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch products',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

       /**
     * GET /api/products/{id}
     */
    public function show($id)
    {
        try {
            $product = Product::where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($product, Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Product show error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to fetch product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * POST /api/products
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'quantity' => ['required', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            $product = Product::create($validated);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product,
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            Log::error('Product store error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], Response::HTTP_NOT_FOUND);
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

            return response()->json([
              
                'data' => $product,
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Product update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * DELETE /api/products/{id}
     */
    public function destroy($id)
    {
        try {
            $product = Product::where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $product->update([
                'is_deleted' => true,
            ]);

            return response()->json([
                'message' => 'Product deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Product delete error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
