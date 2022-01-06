<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\SubscriptionPlan|\App\Models\SubscriptionPlan[] make(array $attributes = [])
 * @method \App\Models\SubscriptionPlan|\App\Models\SubscriptionPlan[] create(array $attributes = [])
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->unique()->text,
            'price' => $this->faker->unique()->randomFloat(2, 5, 10),
        ];
    }
}
