<?php

namespace Database\Factories;

use App\Models\SubscriptionRequest;
use App\Models\Designer;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionRequest>
 */
class SubscriptionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'designer_id' => Designer::factory(),
            'pricing_plan_id' => PricingPlan::factory(),
            'status' => SubscriptionRequest::STATUS_PENDING,
            'notes' => fake('ar_SA')->sentence(),
            'payment_proof_path' => '/subscription-requests/proofs/' . fake()->uuid() . '.jpg',
            'payment_proof_original_name' => 'payment_proof.jpg',
            'payment_proof_size' => fake()->numberBetween(100000, 5000000),
            'payment_proof_mime_type' => 'image/jpeg',
            'subscription_price' => fake()->randomFloat(2, 3000, 15000),
            'requested_start_date' => now()->addDays(1),
        ];
    }

    /**
     * Indicate that the request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'reviewed_by' => User::factory()->create(['user_type' => 'admin']),
            'reviewed_at' => now(),
            'admin_notes' => 'تم الموافقة على الطلب',
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionRequest::STATUS_REJECTED,
            'reviewed_by' => User::factory()->create(['user_type' => 'admin']),
            'reviewed_at' => now(),
            'admin_notes' => 'تم رفض الطلب',
        ]);
    }
} 