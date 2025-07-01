<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\User;
use App\Models\Rachma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['user_type' => 'client']),
            'target_id' => Rachma::factory(),
            'target_type' => 'rachma',
            'rating' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Indicate that the rating is for a store.
     */
    public function forStore(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'store',
        ]);
    }
} 