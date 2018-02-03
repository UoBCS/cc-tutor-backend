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
        $courseId = DB::table('courses')->where('title', 'Automata')->first()->id;
        $stateInstructions = [];
        $finiteAutomatonInstructions = [];

        DB::table('lessons')->insert([
            [
                'index'        => 0,
                'title'        => 'State',
                'description'  => 'Implement the state class',
                'instructions' => json_encode($stateInstructions),
                'course_id'    => $courseId
            ],

            [
                'index'        => 1,
                'title'        => 'Finite Automaton',
                'description'  => 'Implement the finite automaton class',
                'instructions' => json_encode($finiteAutomatonInstructions),
                'course_id'    => $courseId
            ]
        ]);
    }
}
