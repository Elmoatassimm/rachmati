<?php

namespace Database\Factories;

use App\Models\PricingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingPlan>
 */
class PricingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $months = fake()->randomElement([1, 3, 6, 12]);
        $basePrice = 5000;
        $price = $basePrice * $months * 0.9; // Discount for longer plans
        
        return [
            'name' => fake()->randomElement(['خطة شهرية', 'خطة ربع سنوية', 'خطة نصف سنوية', 'خطة سنوية']),
            'duration_months' => $months,
            'price' => $price,
            'description' => fake('ar_SA')->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the pricing plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 