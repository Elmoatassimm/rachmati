<?php

namespace Database\Factories;

use App\Models\Designer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Designer>
 */
class DesignerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['user_type' => 'designer']),
            'store_name' => fake('ar_SA')->company() . ' للتصاميم',
            'store_description' => fake('ar_SA')->paragraph(),
            'subscription_status' => 'active',
            'subscription_start_date' => now()->subDays(30),
            'subscription_end_date' => now()->addDays(335),
            'earnings' => fake()->randomFloat(2, 1000, 50000),
            'paid_earnings' => fake()->randomFloat(2, 500, 25000),
            'subscription_price' => fake()->randomFloat(2, 3000, 15000),
        ];
    }

    /**
     * Indicate that the designer has an inactive subscription.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'inactive',
            'subscription_start_date' => null,
            'subscription_end_date' => null,
        ]);
    }

    /**
     * Indicate that the designer subscription is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'pending',
            'subscription_start_date' => null,
            'subscription_end_date' => null,
        ]);
    }

    /**
     * Indicate that the designer subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'expired',
            'subscription_end_date' => now()->subDays(10),
        ]);
    }
} 