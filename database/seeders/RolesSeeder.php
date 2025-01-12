<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        DB::table('roles')->insert([
            'id' => 2,
            'name' => 'user',
            'guard_name' => 'web',
        ]);
    }
}
