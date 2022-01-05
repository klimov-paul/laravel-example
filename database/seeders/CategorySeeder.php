<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('categories')->count() > 0) {
            return;
        }

        DB::table('categories')->insert([
            [
                'name' => 'Sci-Fi',
                'description' => 'Sci-Fi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Adventure',
                'description' => 'Adventure',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Education',
                'description' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Romance',
                'description' => 'Romance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
