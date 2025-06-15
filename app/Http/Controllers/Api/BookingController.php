<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Route;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException as StripeApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = Booking::with(['user', 'route', 'vehicle', 'driver', 'trip'])->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    protected StripeClient $stripe;

    public function __construct(StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'route_id' => 'required|exists:routes,id',
            'departure_time' => 'required|date|after:now',
            'passengers' => 'required|integer|min:1',
            'payment_method_id' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $route = Route::findOrFail($validated['route_id']);
        $totalFare = $route->base_fare * $validated['passengers'];

        return DB::transaction(function () use ($validated, $route, $totalFare) {
            // Create the booking
            $booking = Booking::create([
                'user_id' => $validated['user_id'],
                'route_id' => $validated['route_id'],
                'departure_time' => $validated['departure_time'],
                'passengers' => $validated['passengers'],
                'total_fare' => $totalFare,
                'notes' => $validated['notes'] ?? null,
                'status' => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
            ]);

            try {
                // Create payment intent
                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => (int)($totalFare * 100), // Convert to cents and ensure integer
                    'currency' => 'kes',
                    'payment_method' => $validated['payment_method_id'],
                    'confirmation_method' => 'manual',
                    'confirm' => true,
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'user_id' => $validated['user_id'],
                    ],
                    'description' => "Payment for booking #{$booking->id}",
                ]);

                // Create payment record
                $payment = $booking->payments()->create([
                    'user_id' => $validated['user_id'],
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $totalFare,
                    'currency' => 'KES',
                    'status' => $paymentIntent->status,
                    'payment_method' => $paymentIntent->payment_method_types[0] ?? null,
                    'payment_details' => $paymentIntent->toArray(),
                    'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
                ]);

                // Update booking status based on payment status
                if ($paymentIntent->status === 'succeeded') {
                    $booking->markAsPaid();
                }

                $booking->load(['user', 'route', 'payments']);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'requires_payment_action' => $paymentIntent->status === 'requires_action',
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'data' => $booking
                ], 201);

            } catch (CardException $e) {
                // Handle card errors
                $errorMessage = $e->getError()->message ?? 'Card was declined';
                return $this->handlePaymentError($booking, $errorMessage);
            } catch (RateLimitException $e) {
                // Handle rate limit errors
                return $this->handlePaymentError($booking, 'Too many requests to payment processor. Please try again later.');
            } catch (InvalidRequestException $e) {
                // Handle invalid request errors
                return $this->handlePaymentError($booking, 'Invalid payment request: ' . $e->getMessage());
            } catch (AuthenticationException $e) {
                // Handle authentication errors
                return $this->handlePaymentError($booking, 'Payment authentication failed. Please try again.');
            } catch (ApiConnectionException $e) {
                // Handle API connection errors
                return $this->handlePaymentError($booking, 'Network error while processing payment. Please check your connection.');
            } catch (StripeApiErrorException $e) {
                // Handle Stripe API errors
                return $this->handlePaymentError($booking, 'Payment processing error: ' . $e->getMessage());
            } catch (\Exception $e) {
                // Handle any other errors
                return $this->handlePaymentError($booking, 'An unexpected error occurred: ' . $e->getMessage());
            }
        });
    }

    /**
     * Handle payment errors by updating the booking status and returning an error response
     */
    protected function handlePaymentError(Booking $booking, string $errorMessage): JsonResponse
    {
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => Booking::PAYMENT_STATUS_FAILED
        ]);

        return response()->json([
            'success' => false,
            'message' => $errorMessage,
        ], 400);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['user', 'route', 'vehicle', 'driver', 'trip']);
        
        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'status' => 'sometimes|in:pending,confirmed,in_progress,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $booking->update($request->all());
        $booking->load(['user', 'route', 'vehicle', 'driver']);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking
        ]);
    }

    public function cancel(Booking $booking): JsonResponse
    {
        if ($booking->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel completed booking'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }
}
