<?php

namespace Database\Seeders;

use App\Enums\AdminRoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run()
    {
        if (DB::table('admins')->count() > 0) {
            return;
        }

        DB::table('admins')->insert([
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('secret'),
                'role' => AdminRoleEnum::MASTER_ADMIN,
                'remember_token' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
