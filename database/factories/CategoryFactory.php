<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\Category|\App\Models\Category[] make(array $attributes = [])
 * @method \App\Models\Category|\App\Models\Category[] create(array $attributes = [])
 */
class CategoryFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $name = $this->faker->title();

        return [
            'name' => $name,
            'description' => $name . ' description',
        ];
    }
}
