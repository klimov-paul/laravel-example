<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\Payment|\App\Models\Payment[] make(array $attributes = [])
 * @method \App\Models\Payment|\App\Models\Payment[] create(array $attributes = [])
 */
class PaymentFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'payment_method_id' => null, // must be passed explicitly on factory call
            'type' => PaymentType::SUBSCRIPTION,
            'status' => PaymentStatus::SUCCESS,
            'amount' => $this->faker->randomFloat(2, 10, 100),
            'details' => '[]',
        ];
    }
}
