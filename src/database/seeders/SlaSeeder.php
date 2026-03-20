<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('slas')->insert([
            [
                'name' => 'Alta',
                'response_time' => 30,
                'resolution_time' => 240,
            ],
            [
                'name' => 'Media',
                'response_time' => 60,
                'resolution_time' => 480,
            ],
            [
                'name' => 'Baja',
                'response_time' => 240,
                'resolution_time' => 1440,
            ],
        ]);
    }
}
