<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Service\PaymentIntentService;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;

class BookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set a valid test API key format
        config([
            'services.stripe.secret' => 'sk_test_'.str_repeat('a', 24),
            'services.stripe.key' => 'pk_test_'.str_repeat('a', 24),
        ]);
        
        // Mock the Stripe client
        $this->mockStripeClient();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Close any mockery mocks
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        Mockery::close();
    }
    
    protected function mockStripeClient(): void
    {
        $stripeMock = Mockery::mock('overload:' . StripeClient::class);
        $paymentIntentsService = Mockery::mock(PaymentIntentService::class);
        
        $stripeMock->shouldReceive('__get')
            ->with('paymentIntents')
            ->andReturn($paymentIntentsService);
            
        $this->app->instance(StripeClient::class, $stripeMock);
        
        $this->stripeMock = $stripeMock;
        $this->paymentIntentsService = $paymentIntentsService;
    }

    protected $stripeMock;
    protected $paymentIntentsService;
    
    public function test_booking_creation_with_payment()
    {
        // Create test data
        $user = User::factory()->create();
        $route = Route::factory()->create();
        
        // Create a mock payment intent
        $paymentIntentId = 'pi_'.str_repeat('a', 24);
        $clientSecret = 'pi_'.str_repeat('a', 24).'_secret_'.str_repeat('a', 16);
        
        // Mock the payment intent creation
        $this->paymentIntentsService
            ->shouldReceive('create')
            ->once()
            ->with([
                'amount' => $route->base_fare * 2 * 100, // 2 passengers
                'currency' => 'kes',
                'payment_method' => Mockery::pattern('/^pm_/'),
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'booking_id' => Mockery::type('int'),
                    'user_id' => $user->id,
                ],
                'description' => Mockery::pattern('/^Payment for booking #\d+$/'),
            ])
            ->andReturn((object)[
                'id' => $paymentIntentId,
                'status' => 'succeeded',
                'client_secret' => $clientSecret,
                'payment_method_types' => ['card'],
                'toArray' => fn() => [],
            ]);
        
        // Make booking request
        $response = $this->actingAs($user)->postJson('/api/v1/bookings', [
            'user_id' => $user->id,
            'route_id' => $route->id,
            'departure_time' => now()->addDay()->toDateTimeString(),
            'passengers' => 2,
            'payment_method_id' => 'pm_'.Str::random(24),
            'notes' => 'Test booking with payment',
        ]);

        // Dump response for debugging
        $responseData = $response->json();
        dump('Response content:', $responseData);
        
        // Assert response
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Booking created successfully',
                'requires_payment_action' => false,
            ]);

        // Assert the booking was created with the correct status
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'route_id' => $route->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_booking_creation_with_3d_secure()
    {
        // Create test data
        $user = User::factory()->create();
        $route = Route::factory()->create();
        
        // Create a mock payment intent that requires 3D Secure
        $paymentIntentId = 'pi_'.str_repeat('b', 24);
        $clientSecret = 'pi_'.str_repeat('b', 24).'_secret_'.str_repeat('b', 16);
        
        // Mock the payment intent creation
        $this->paymentIntentsService
            ->shouldReceive('create')
            ->once()
            ->with([
                'amount' => $route->base_fare * 1 * 100, // 1 passenger
                'currency' => 'kes',
                'payment_method' => Mockery::pattern('/^pm_/'),
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'booking_id' => Mockery::type('int'),
                    'user_id' => $user->id,
                ],
                'description' => Mockery::pattern('/^Payment for booking #\d+$/'),
            ])
            ->andReturn((object)[
                'id' => $paymentIntentId,
                'status' => 'requires_action',
                'client_secret' => $clientSecret,
                'payment_method_types' => ['card'],
                'next_action' => ['type' => 'use_stripe_sdk'],
                'toArray' => fn() => [],
            ]);
        
        // Make booking request
        $response = $this->actingAs($user)->postJson('/api/v1/bookings', [
            'user_id' => $user->id,
            'route_id' => $route->id,
            'departure_time' => now()->addDay()->toDateTimeString(),
            'passengers' => 1,
            'payment_method_id' => 'pm_'.Str::random(24),
            'notes' => 'Test booking with 3D Secure',
        ]);

        // Dump response for debugging
        $responseData = $response->json();
        dump('3D Secure Response content:', $responseData);
        
        // Assert response indicates payment requires action
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'requires_payment_action' => true,
                'client_secret' => $clientSecret,
            ]);

        // Assert the booking was created with the correct status
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'route_id' => $route->id,
            'status' => 'pending',
            'payment_status' => 'requires_action',
        ]);
    }
}
