<?php

namespace Database\Factories;

use App\Models\Good;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
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
            'good_id' => Good::inRandomOrder()->value('id'),
            'order_id' => Order::inRandomOrder()->value('id'),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->numberBetween(100, 10000),
        ];
    }
}
