<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'ჯგუფი 1 - 09:00 - შესვენება 1 საათი',
                'control' => true,
                'break_control' => true,
                'leave_control' => true
            ],
            [
                'name' => 'ჯგუფი 2 - დილა - საღამო',
                'control' => true,
                'break_control' => false,
                'leave_control' => true
            ],
            [
                'name' => 'ჯგუფი 3 - დილით მოსვლა',
                'control' => true,
                'break_control' => false,
                'leave_control' => false
            ],
            [
                'name' => 'ჯგუფი 4 - შეუძლებელია კონტროლი',
                'control' => false,
                'break_control' => false,
                'leave_control' => false
            ],
            [
                'name' => 'ჯგუფი 5 - თავისუფალი განრიგი',
                'control' => false,
                'break_control' => false,
                'leave_control' => true
            ],
            [
                'name' => 'ჯგუფი 6 - მუშაობენ ცვლებში',
                'control' => false,
                'break_control' => false,
                'leave_control' => true
            ],
            [
                'name' => 'სხვა გრაფიკი',
                'control' => true,
                'break_control' => true,
                'leave_control' => true
            ]
        ];

        DB::table('groups')->insert($groups);
    }
}
