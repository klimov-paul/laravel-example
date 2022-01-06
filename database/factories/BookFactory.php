<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\Book|\App\Models\Book[] make(array $attributes = [])
 * @method \App\Models\Book|\App\Models\Book[] create(array $attributes = [])
 */
class BookFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $title = $this->faker->title();

        return [
            'isbn' => uniqid(),
            'title' => $title,
            'description' => $title . ' description',
            'author' => $this->faker->name(),
            'price' => rand(10, 500),
        ];
    }
}
