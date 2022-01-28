<?php

namespace Database\Factories;

use App\Enums\AdminRoleEnum;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @method \App\Models\Admin|\App\Models\Admin[] make(array $attributes = [])
 * @method \App\Models\Admin|\App\Models\Admin[] create(array $attributes = [])
 */
class AdminFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Admin::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('secret'),
            'role' => AdminRoleEnum::MASTER_ADMIN,
            'remember_token' => Str::random(10),
        ];
    }
}
