<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tags')->insert([
            ['name' => 'bug'],
            ['name' => 'urgente'],
            ['name' => 'login'],
            ['name' => 'api'],
            ['name' => 'frontend'],
            ['name' => 'backend'],
        ]);
    }
}
