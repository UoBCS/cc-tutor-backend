<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('courses')->insert([
            [
                'title'       => 'Automata',
                'subtitle'    => 'Automata classes',
                'description' => 'In this course you will be building the suite for working with finite automata'
            ]
        ]);
    }
}
