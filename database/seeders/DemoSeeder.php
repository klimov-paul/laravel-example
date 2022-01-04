<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DemoSeeder fills up database with the dummy data for the test purposes.
 *
 * > Attention: This seeder should not be invoked at production.
 *
 * ```
 * php artisan db:seed --class DemoSeeder
 * ```
 */
class DemoSeeder extends Seeder
{
    public function run()
    {
        $user = User::query()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('secret'),
        ]);

        // ...
    }
}
