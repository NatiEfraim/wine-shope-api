<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingStatusUpdatedMail;

class BookingController extends Controller
{
    /**
     * POST /api/bookings
     * Create booking with many booking items
     */
    public function store(StoreBookingRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $user = Auth::guard('api')->user();
            $userId = $user ? $user->id : null;
            $pendingStatus = Status::where('name', 'pending')->first();

            $booking = Booking::create([
                'user_id' => $userId,
                'guest_name' => $userId ? null : ($validated['guest_name'] ?? null),
                'guest_email' => $userId ? null : ($validated['guest_email'] ?? null),
                'guest_phone' => $userId ? null : ($validated['guest_phone'] ?? null),
                'guest_personal_id' => $userId ? null : ($validated['guest_personal_id'] ?? null),
                'status_id' => $pendingStatus->id,
                'total_price' => 0,
                'is_deleted' => false,
            ]);

            $bookingTotal = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::where('is_deleted', false)->where('is_active', true)->where('id', $item['product_id'])->lockForUpdate()->first();

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Not enough quantity for product: {$product->name}");
                }

                $unitPrice = $product->price_after_discount;
                $totalPrice = $unitPrice * $item['quantity'];

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                $product->decrement('quantity', $item['quantity']);
                $bookingTotal += $totalPrice;
            }

            $booking->update([
                'total_price' => $bookingTotal,
            ]);

            DB::commit();

            $booking = Booking::with(['user', 'status', 'items.product'])->find($booking->id);
            
            $email = $user ? $user->email : $booking->guest_email;
            
            if (!empty($email)) {
                Mail::to($email)->send(new BookingCreatedMail($booking));
            }
            
            return response()->json([
                'message' => 'Booking created successfully',
            ], Response::HTTP_CREATED);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to created booking',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT /api/bookings/{id}
     * Update booking status
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status_id' => ['required', 'integer', 'exists:statuses,id'],
            ]);

            $booking = Booking::with(['user'])->where('is_deleted', false)->where('id', $id)->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $booking->update([
                'status_id' => $validated['status_id'],
            ]);

            $email = $booking->user_id ? $booking->user->email : $booking->guest_email;
            
            if (!empty($email)) {
                Mail::to($email)->send(new BookingStatusUpdatedMail($booking));
            }
            
            return response()->json([
                'message' => 'Booking updated successfully',
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Booking update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update booking',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/bookings/my-bookings
     * Get authenticated user bookings
     */
    public function myBookings(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Booking::with(['status', 'items.product'])
                ->where('is_deleted', false)
                ->where('user_id', $user->id);

            if ($request->filled('serial_number')) {
                $query->where('serial_number', 'like', '%' . $request->serial_number . '%');
            }

            if ($request->filled('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            $bookings = $query->latest()->get();

            return response()->json([
                'data' => $bookings,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('My bookings error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch my bookings',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/bookings
     * Admin / Moderator get all bookings
     */
    public function index(Request $request)
    {
        try {
            $query = Booking::with(['user', 'status', 'items.product'])->where('is_deleted', false);

            if ($request->filled('personal_id')) {
                $user = User::where('personal_id', $request->personal_id)->first();
                if (!$user) {
                    return response()->json([
                        'message' => 'User not found',
                        'data' => [],
                    ], Response::HTTP_OK);
                }
                $query->where('user_id', $user->id);
            }

            if ($request->filled('serial_number')) {
                $query->where('serial_number', 'like', '%' . $request->serial_number . '%');
            }

            if ($request->filled('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            $bookings = $query->latest()->get()->map(function($booking) {
                if (!$booking->user_id) {
                    $booking->setAttribute('user', [
                        'name' => $booking->guest_name . ' (אורח)',
                        'email' => $booking->guest_email,
                        'personal_id' => $booking->guest_personal_id,
                        'phone' => $booking->guest_phone,
                    ]);
                }
                return $booking;
            });

            return response()->json([
                'message' => 'Bookings fetched successfully',
                'data' => $bookings,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Bookings index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch bookings',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $booking = Booking::with(['user', 'status', 'items.product'])
                ->where('is_deleted', false)
                ->where('id', $id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found',
                ], Response::HTTP_NOT_FOUND);
            }

            if (!$booking->user_id) {
                $booking->setAttribute('user', [
                    'name' => $booking->guest_name . ' (אורח)',
                    'email' => $booking->guest_email,
                    'personal_id' => $booking->guest_personal_id,
                    'phone' => $booking->guest_phone,
                ]);
            }

            return response()->json([
                'message' => 'Booking fetched successfully',
                'data' => $booking,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Booking show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch booking',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * DELETE /api/bookings/{id}
     */
    public function destroy($id)
    {
        try {
            $booking = Booking::where('is_deleted', false)->where('id', $id)->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $booking->update([
                'is_deleted' => true,
            ]);

            return response()->json([
                'message' => 'Booking deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Booking delete error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete booking',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}