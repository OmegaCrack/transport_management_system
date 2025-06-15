<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Display a listing of payments for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Create a new payment intent for a booking.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'payment_method_id' => 'required|string',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $user = $request->user();

        try {
            // Create a PaymentIntent with amount and currency
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $request->amount * 100, // Convert to cents
                'currency' => strtolower($request->currency),
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                ],
                'description' => "Payment for booking #{$booking->id}",
            ]);

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => $paymentIntent->status,
                'payment_method' => $paymentIntent->payment_method_types[0] ?? null,
                'payment_details' => $paymentIntent->toArray(),
                'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
            ]);

            // Update booking status if payment is successful
            if ($paymentIntent->status === 'succeeded') {
                $booking->update(['payment_status' => 'paid']);
            }

            return response()->json([
                'success' => true,
                'requires_action' => $paymentIntent->status === 'requires_action',
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'payment' => $payment->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm a payment intent that requires additional action.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($request->payment_intent_id);
            $payment = Payment::where('payment_intent_id', $paymentIntent->id)->firstOrFail();

            // Update payment status
            $payment->update([
                'status' => $paymentIntent->status,
                'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
            ]);

            // Update booking status if payment is successful
            if ($paymentIntent->status === 'succeeded') {
                $payment->booking()->update(['payment_status' => 'paid']);
            }

            return response()->json([
                'success' => true,
                'payment' => $payment->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(string $id): JsonResponse
    {
        $payment = Payment::with(['booking', 'user'])->findOrFail($id);

        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== request()->user()->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
