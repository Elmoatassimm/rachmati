<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Rachma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
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
            'comment' => fake('ar_SA')->paragraph(),
        ];
    }

    /**
     * Indicate that the comment is for a store.
     */
    public function forStore(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'store',
        ]);
    }
} 