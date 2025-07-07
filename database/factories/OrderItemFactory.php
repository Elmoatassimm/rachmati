<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rachma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'rachma_id' => Rachma::factory(),
            'price' => fake()->randomFloat(2, 1000, 10000),
        ];
    }

    /**
     * Create an order item with specific price
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    /**
     * Create an order item for a specific order
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Create an order item for a specific rachma
     */
    public function forRachma(Rachma $rachma): static
    {
        return $this->state(fn (array $attributes) => [
            'rachma_id' => $rachma->id,
            'price' => $rachma->price,
        ]);
    }
}
