<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForgiveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forgiveTypes = [
            ['name' => 'არასაპატიო', 'forgive' => false],
            ['name' => 'პირადი საპატიო', 'forgive' => true],
            ['name' => 'სამსახურებრივი საქმე', 'forgive' => true],
            ['name' => 'ბარათის დარჩენა', 'forgive' => true],
            ['name' => 'მივლინება', 'forgive' => true],
            ['name' => 'დასვენების დღე', 'forgive' => true],
            ['name' => 'სხვა', 'forgive' => true],
            ['name' => 'არასაპატიო ავტომატურად', 'forgive' => false],
            ['name' => 'ცვლის გადაცვლა', 'forgive' => true],
            ['name' => 'დისტანციურად მუშაობა', 'forgive' => true],
            ['name' => 'შვებულება', 'forgive' => true],
            ['name' => 'საავადმყოფო ფურცელი', 'forgive' => true],
        ];

        DB::table('forgive_types')->insert($forgiveTypes);
    }
}
