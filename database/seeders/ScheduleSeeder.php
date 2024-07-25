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
                'name' => 'Morning Shift',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Morning shift for all employees',
                'day_start' => '09:00',
                'day_end' => '17:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evening Shift',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Evening shift for all employees',
                'day_start' => '13:00',
                'day_end' => '21:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Night Shift',
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date' => Carbon::parse('2024-07-31'),
                'repetition_unit' => 1,
                'interval' => 1,
                'comment' => 'Night shift for all employees',
                'day_start' => '21:00',
                'day_end' => '05:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
