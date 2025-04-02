<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1000, 50000), // Random transaction amount
            'status' => $this->faker->randomElement([
                'send_to_bank', 'paid', 'failed', 'canceled',
                'reversed', 'pending', 'blocked', 'under_review'
            ]),
            'gateway_name' => $this->faker->randomElement(['PayPal', 'Zarinpal', 'Mellat']),
            'response_code' => $this->faker->optional()->numberBetween(10, 17),
            'order_id' => $this->faker->unique()->numberBetween(1000, 9999),
            'transaction_code' => $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->sentence(),
            'creator_id' => 1, // Assuming a user is required
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null, // For soft deletes
        ];
    }
}
