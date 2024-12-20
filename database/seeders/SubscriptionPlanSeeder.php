<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('subscription_plans')->count() > 0) {
            return;
        }

        DB::table('subscription_plans')->insert([
            [
                'name' => 'Basic',
                'description' => 'Basic plan',
                'price' => 10,
                'max_book_price' => 5,
                'max_rent_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Advanced',
                'description' => 'Advanced plan',
                'price' => 18,
                'max_book_price' => 20,
                'max_rent_count' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
