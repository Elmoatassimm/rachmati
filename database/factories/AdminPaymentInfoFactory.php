<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminPaymentInfo>
 */
class AdminPaymentInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ccp_number' => $this->faker->numerify('##########'),
            'ccp_key' => $this->faker->numerify('##'),
            'nom' => $this->faker->name(),
            'adress' => $this->faker->address(),
            'baridimob' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Indicate that the payment info has empty fields.
     */
    public function withEmptyFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'ccp_number' => '',
            'nom' => '',
            'baridimob' => '',
        ]);
    }
} 