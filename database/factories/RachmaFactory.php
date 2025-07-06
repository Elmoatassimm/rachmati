<?php

namespace Database\Factories;

use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rachma>
 */
class RachmaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(2500, 25000);
        
        return [
            'designer_id' => Designer::factory(),
            'title_ar' => fake()->sentence(),
            'title_fr' => fake()->sentence(),
            'description_ar' => fake()->paragraph(),
            'description_fr' => fake()->paragraph(),
            'color_numbers' => fake()->numberBetween(1, 10),
            'price' => $price,
            'preview_images' => null,
            'files' => null,
            'file_path' => null,
            'average_rating' => null,
            'ratings_count' => 0,
            'is_active' => true,
        ];
    }
} 