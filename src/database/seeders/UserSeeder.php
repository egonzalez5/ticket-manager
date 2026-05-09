<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        $agentId = DB::table('roles')->where('slug', 'agent')->value('id');
        $userId  = DB::table('roles')->where('slug', 'user')->value('id');

        DB::table('users')->insert([
            [
                'name'     => 'Admin',
                'email'    => 'admin@test.com',
                'password' => Hash::make('123456'),
                'role_id'  => $adminId,
            ],
            [
                'name'     => 'Agent',
                'email'    => 'agent@test.com',
                'password' => Hash::make('123456'),
                'role_id'  => $agentId,
            ],
            [
                'name'     => 'User',
                'email'    => 'user@test.com',
                'password' => Hash::make('123456'),
                'role_id'  => $userId,
            ],
        ]);
    }
}
