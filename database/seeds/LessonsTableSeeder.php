<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $automataCourseId = DB::table('courses')->where('title', 'Automata')->first()->id;
        $regexInstructions = [];
        $stateInstructions = [];
        $finiteAutomatonInstructions = [];

        DB::table('lessons')->insert([
            [
                'index'        => 0,
                'title'        => 'Regex',
                'description'  => 'Implement the regex classes',
                'instructions' => json_encode($regexInstructions),
                'course_id'    => $automataCourseId
            ],

            [
                'index'        => 1,
                'title'        => 'State',
                'description'  => 'Implement the state class',
                'instructions' => json_encode($stateInstructions),
                'course_id'    => $automataCourseId
            ],

            [
                'index'        => 2,
                'title'        => 'Finite Automaton',
                'description'  => 'Implement the finite automaton class',
                'instructions' => json_encode($finiteAutomatonInstructions),
                'course_id'    => $automataCourseId
            ]
        ]);
    }
}
