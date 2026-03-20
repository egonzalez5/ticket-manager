<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ticket_statuses')->insert([
            ['name' => 'Open'],
            ['name' => 'In Progress'],
            ['name' => 'Pending'],
            ['name' => 'Resolved'],
            ['name' => 'Closed'],
        ]);
    }
}
