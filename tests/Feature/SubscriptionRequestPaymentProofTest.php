<?php

namespace Tests\Feature;

use App\Models\SubscriptionRequest;
use App\Models\Designer;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionRequestPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test storage disk
        Storage::fake('public');
    }

    /** @test */
    public function subscription_request_returns_payment_proof_url_when_path_exists()
    {
        // Create a designer and pricing plan
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $pricingPlan = PricingPlan::factory()->create();

        // Create subscription request with payment proof
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_path' => 'subscription-proofs/test-payment-proof.jpg',
            'payment_proof_original_name' => 'payment_proof.jpg',
            'payment_proof_size' => 1024000,
            'payment_proof_mime_type' => 'image/jpeg',
        ]);

        // Create a fake file in storage
        Storage::disk('public')->put('subscription-proofs/test-payment-proof.jpg', 'fake image content');

        // Test that payment_proof_url is generated correctly
        $this->assertNotNull($subscriptionRequest->payment_proof_url);
        $this->assertStringContains('subscription-proofs/test-payment-proof.jpg', $subscriptionRequest->payment_proof_url);
    }

    /** @test */
    public function subscription_request_returns_null_payment_proof_url_when_no_path()
    {
        // Create a designer and pricing plan
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $pricingPlan = PricingPlan::factory()->create();

        // Create subscription request without payment proof
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_path' => null,
        ]);

        // Test that payment_proof_url is null
        $this->assertNull($subscriptionRequest->payment_proof_url);
    }

    /** @test */
    public function subscription_request_returns_formatted_file_size()
    {
        // Create a designer and pricing plan
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $pricingPlan = PricingPlan::factory()->create();

        // Create subscription request with payment proof
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_size' => 1024000, // 1MB
        ]);

        // Test that formatted_file_size is generated correctly
        $this->assertEquals('1000 KB', $subscriptionRequest->formatted_file_size);
    }

    /** @test */
    public function subscription_request_returns_has_payment_proof_correctly()
    {
        // Create a designer and pricing plan
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $pricingPlan = PricingPlan::factory()->create();

        // Create subscription request with payment proof
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_path' => 'subscription-proofs/test-payment-proof.jpg',
        ]);

        // Create a fake file in storage
        Storage::disk('public')->put('subscription-proofs/test-payment-proof.jpg', 'fake image content');

        // Test that has_payment_proof returns true
        $this->assertTrue($subscriptionRequest->has_payment_proof);

        // Test without file
        $subscriptionRequestWithoutFile = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_path' => null,
        ]);

        $this->assertFalse($subscriptionRequestWithoutFile->has_payment_proof);
    }

    /** @test */
    public function subscription_request_appends_computed_attributes_to_array()
    {
        // Create a designer and pricing plan
        $user = User::factory()->create(['user_type' => 'designer']);
        $designer = Designer::factory()->create(['user_id' => $user->id]);
        $pricingPlan = PricingPlan::factory()->create();

        // Create subscription request with payment proof
        $subscriptionRequest = SubscriptionRequest::factory()->create([
            'designer_id' => $designer->id,
            'pricing_plan_id' => $pricingPlan->id,
            'payment_proof_path' => 'subscription-proofs/test-payment-proof.jpg',
            'payment_proof_size' => 1024000,
        ]);

        // Create a fake file in storage
        Storage::disk('public')->put('subscription-proofs/test-payment-proof.jpg', 'fake image content');

        // Convert to array and check appended attributes
        $array = $subscriptionRequest->toArray();

        $this->assertArrayHasKey('payment_proof_url', $array);
        $this->assertArrayHasKey('formatted_file_size', $array);
        $this->assertArrayHasKey('status_label', $array);
        $this->assertArrayHasKey('has_payment_proof', $array);
    }
}
