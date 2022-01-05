<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\Category|\App\Models\Category[] make()
 * @method \App\Models\Category|\App\Models\Category[] create()
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
