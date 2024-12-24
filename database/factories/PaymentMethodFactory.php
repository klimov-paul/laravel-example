<?php

namespace Database\Factories;

use App\Enums\PaymentMethodStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\PaymentMethod|\App\Models\PaymentMethod[] make(array $attributes = [])
 * @method \App\Models\PaymentMethod|\App\Models\PaymentMethod[] create(array $attributes = [])
 */
class PaymentMethodFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'user_id' => null, // must be passed explicitly on factory call
            'customer_id' => uniqid(),
            'card_brand' => $this->faker->creditCardType,
            'card_last_four' => (string) $this->faker->randomNumber(4),
            'status' => PaymentMethodStatus::ACTIVE,
        ];
    }
}
