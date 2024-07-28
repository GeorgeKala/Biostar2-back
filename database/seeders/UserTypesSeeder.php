<?php

namespace Database\Seeders;

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
            ['name' => 'ადმინისტრატორი', 'has_full_access' => true],
            ['name' => 'დეპარტამენტის ხელმძღვანელი', 'has_full_access' => false],
        ];

        foreach ($userTypes as $userType) {
            DB::table('user_types')->insert($userType);
        }
    }
}
