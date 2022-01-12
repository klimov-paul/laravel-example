<?php

namespace Database\Factories;

use App\Enums\CreditCardStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\CreditCard|\App\Models\CreditCard[] make(array $attributes = [])
 * @method \App\Models\CreditCard|\App\Models\CreditCard[] create(array $attributes = [])
 */
class CreditCardFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'user_id' => null, // must be passed explicitly on factory call
            'external_id' => uniqid(),
            'brand' => $this->faker->creditCardType,
            'last_four' => (string) $this->faker->randomNumber(4),
            'status' => CreditCardStatus::ACTIVE,
        ];
    }
}
