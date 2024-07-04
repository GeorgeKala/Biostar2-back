<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userTypes = [
            'ადმინისტრატორი',
            'მენეჯერი',
            'HR',
            'IT',
            'საწყობის უფროსი',
            'მენეჯერი 1',
            'სამზარეულო',
            'ოფის-მენეჯერი',
            'მარკეტი არეული',
            'მენეჯერი-რეგიონები',
            'ლილო',
        ];

        foreach ($userTypes as $userType) {
            DB::table('user_types')->insert([
                'name' => $userType,
            ]);
        }
    }
}
