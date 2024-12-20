<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BookSeeder::class);
        $this->call(SubscriptionPlanSeeder::class);

        if (app()->environment('local')) {
            if (DB::table('users')->count() > 0) {
                return;
            }

            $this->call(DemoSeeder::class);
        }
    }
}
