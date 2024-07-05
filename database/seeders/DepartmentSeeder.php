<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'მარნეულის ფილიალი',
            'წყალსადენის საწყობი',
            'წერეთლის ფილიალი',
            'ლუსონი',
            'ბათუმის ფილიალი',
            'ბათუმის სამეურნეო განყოფილება',
            'გლდანის ფილიალი',
            'თელავის ფილიალი',
            'წერეთელი 1',
            'წერეთელი კიბეები',
            'წერეთელი ორივე',
            'ქუთაისი',
            'ბათუმის ჰიპერმარკეტი',
            'სამეგრელო',
            'ავტოინვესტი',
            'რუსთავი',
            'ლილო',
            'გორის ფილიალი',
            'ვაკე უსაფრთხოება',
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'name' => $department,
            ]);
        }
    }
}
