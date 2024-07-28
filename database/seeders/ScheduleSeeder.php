<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run()
    {
        Schedule::insert([
            [
                'name' => '09:00-18:00 - დიდუბე ადმინისტრაცია',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Morning shift for all employees',
                'day_start' => '09:00',
                'day_end' => '18:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '09:30-18:30 - დიდუბე ადმინისტრაცია',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Evening shift for all employees',
                'day_start' => '09:30',
                'day_end' => '18:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '10:00-19:00 - დიდუბე ადმინისტრაცია',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Night shift for all employees',
                'day_start' => '10:00',
                'day_end' => '19:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '10:00-19:00 - დიდუბე ადმინისტრაცია',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Night shift for all employees',
                'day_start' => '10:00',
                'day_end' => '19:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'დიდუბე - გლდანი (ფილიალები)',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Night shift for all employees',
                'day_start' => '09:00',
                'day_end' => '21:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
