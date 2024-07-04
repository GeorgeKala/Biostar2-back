<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $buildingNames = [
            'მაენეულის შენობა',
            'თბილისი წერეთელი',
            'წყალსადენის საწყობი',
            'გლდანის შენობა',
            'ზუგდიდის შენობა',
            'თელავის შენობა',
            'ლუსონის შენობა',
            'ზუგდიდის საწყობი',
            'ქუთაისი',
            'ბათუმი',
            'საბურთალო',
            'ავტოინვესტი-ავჭალა',
            'ავტოინვესტი-ქუთაისი',
            'რუსთავი მოლი',
            'ლილო',
            'სარაჯიშვილი',
            'გორი',
            'ვაკე',
        ];

        foreach ($buildingNames as $name) {
            DB::table('buildings')->insert([
                'name' => $name,
            ]);
        }
    }
}
