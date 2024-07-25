<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DayTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dayTypes = [
            'არა სამუშაო დღე',
            'გაცდენა',
            'დეკრეტში გასვლა',
            'დისტანციური მუშაობა',
            'საავადმყოფო დღე',
            'სამუშაო დღე',
            'შვებულება',
            'შვებულება ხელფასის გარეშე',
        ];

        foreach ($dayTypes as $type) {
            DB::table('day_types')->insert([
                'name' => $type,
            ]);
        }
    }
}
