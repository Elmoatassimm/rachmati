<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameAr = fake('ar_SA')->words(2, true);
        $nameFr = fake('fr_FR')->words(2, true);

        return [
            'name_ar' => $nameAr,
            'name_fr' => $nameFr,
        ];
    }
} 