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
        $price = fake()->randomFloat(2, 2000, 20000);
        
        return [
            'designer_id' => Designer::factory(),
            'title' => fake('ar_SA')->sentence(3),
            'description' => fake('ar_SA')->paragraph(),
            'file_path' => '/rachmat/files/' . fake()->uuid() . '.pdf',
            'preview_images' => [
                '/images/preview1.jpg',
                '/images/preview2.jpg',
            ],
            'size' => fake()->randomElement(['20x25 cm', '25x30 cm', '30x35 cm', '35x40 cm']),
            'gharazat' => fake()->numberBetween(8000, 25000),
            'color_numbers' => ['001', '002', '025', '150', '208'],
            'price' => $price,
            'original_price' => $price,
            'average_rating' => fake()->randomFloat(1, 3.0, 5.0),
            'ratings_count' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
} 