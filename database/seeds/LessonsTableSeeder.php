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
        $regexInstructions = [
            'learn' => "
            <p>In this lesson you will implement the RegexParser.java class using the provided types.</p>
            <p>The regular expression grammar is the following:</p>
            <pre>regex ::= term '|' regex | term</pre>
            <pre>term ::= { factor }</pre>
            <pre>factor ::= base { '*' }</pre>
            <pre>base ::= char | '\' char | '(' regex ')'</pre>
            ",
            'instructions' => '<p>Implement the RegexParser.java class</p>',
            'report_bug' => '<p>If you find any bug please report <a href="https://github.com/UoBCS/cc-tutor-frontend/issues/new">here</a>.</p>'
        ];
        $stateInstructions = [
            'learn' => "
            <p>A state is the essential part in a finite automaton.</p>
            <p>A state can be final, can contain data and has other states associated to it through transitions.</p>
            ",
            'instructions' => '<p>Implement the State.java class</p>',
            'report_bug' => '<p>If you find any bug please report <a href="https://github.com/UoBCS/cc-tutor-frontend/issues/new">here</a>.</p>'
        ];
        $finiteAutomatonInstructions = [
            'learn' => "
            <p>A finite automaton is a mathematical model of computation. It is an abstract machine that can be in exactly one of a finite number of states at any given time.</p>
            <p>An FA is defined by a list of its states, its initial state, and the conditions for each transition.</p>
            ",
            'instructions' => '<p>Implement the FiniteAutomaton.java class</p>',
            'report_bug' => '<p>If you find any bug please report <a href="https://github.com/UoBCS/cc-tutor-frontend/issues/new">here</a>.</p>'
        ];

        DB::table('lessons')->insert([
            [
                'index'        => 0,
                'title'        => 'Regex',
                'description'  => 'Implement the regex classes. The main class is RegexParser.java',
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
