<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            'ორშაბათი',
            'სამშაბათი',
            'ოთხშაბათი',
            'ხუთშაბათი',
            'პარასკევი',
            'შაბათი',
            'კვირა',
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert([
                'name' => $holiday,
            ]);
        }
    }
}
