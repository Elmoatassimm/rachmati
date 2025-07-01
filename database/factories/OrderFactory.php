<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Rachma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->create(['user_type' => 'client']),
            'rachma_id' => Rachma::factory(),
            'amount' => fake()->randomFloat(2, 2000, 20000),
            'payment_method' => fake()->randomElement(['ccp', 'baridi_mob', 'dahabiya']),
            'payment_proof_path' => '/orders/proofs/' . fake()->uuid() . '.jpg',
            'status' => 'pending',
            'admin_notes' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'confirmed_at' => now()->subDays(2),
            'file_sent_at' => now()->subDays(1),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => 'سبب الرفض',
            'rejected_at' => now(),
        ]);
    }
} 