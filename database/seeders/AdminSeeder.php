<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'a@a.com';
        $user->password = bcrypt('123123');
        $user->save();
        $user->assignRole(User::SUPER_ADMIN_ROLE_ID);
    }
}
