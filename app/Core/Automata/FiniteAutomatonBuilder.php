<?php

namespace App\Core\Automata;

use App\Core\Inspector;
use App\Core\Syntax\Regex\TreeTypes;
use Exception;

class FiniteAutomatonBuilder
{
    public $entry;
    public $exit;
    private static $inspector;

    public function __construct(State $entry, State $exit)
    {
        $this->entry = $entry;
        $this->exit = $exit;
    }

    public static function init()
    {
        self::$inspector = inspector();
        self::$inspector->createStore('breakpoints', 'array');
        self::$inspector->setRootFn('fromRegexTree');
    }

    public static function c(string $c)
    {
        $entry = new State();
        $exit = new State();
        $exit->setFinal();
        $entry->addTransition($exit, [$c]);

        //$inspector = inspector();
        /* > */ self::$inspector->breakpoint('c', [
        /* > */    'entry'      => $entry,
        /* > */    'transition' => $c,
        /* > */    'exit'       => $exit
        /* > */ ], 'fromRegexTree');

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    public static function e()
    {
        $entry  = new State();
        $exit = new State();
        $entry->addTransition($exit);
        $exit->setFinal();

        //$inspector = inspector();
        /* > */ self::$inspector->breakpoint('e', [
        /* > */    'entry'      => $entry,
        /* > */    'transition' => 'ε',
        /* > */    'exit'       => $exit
        /* > */ ], 'fromRegexTree');

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    public static function rep(self $nfa)
    {
        $nfa->exit->addTransition($nfa->entry);
        $nfa->entry->addTransition($nfa->exit);

        //$inspector = inspector();
        /* > */ self::$inspector->breakpoint('rep', [
        /* > */    'state1' => $nfa->entry,
        /* > */    'state2' => $nfa->exit
        /* > */ ], 'fromRegexTree');

        return $nfa;
    }

    public static function s(self $first, self $second)
    {
        $first->exit->setFinal(false);
        $second->exit->setFinal();
        $first->exit->addTransition($second->entry);

        //$inspector = inspector();
        /* > */ self::$inspector->breakpoint('s', [
        /* > */    'entry'      => $first->exit,
        /* > */    'transition' => 'ε',
        /* > */    'exit'       => $second->entry
        /* > */ ], 'fromRegexTree');

        return new FiniteAutomatonBuilder($first->entry, $second->exit);
    }

    public static function or(self $choice1, self $choice2)
    {
        $choice1->exit->setFinal(false);
        $choice2->exit->setFinal(false);
        $entry = new State();
        $exit  = new State();
        $exit->setFinal();
        $entry->addTransition($choice1->entry);
        $entry->addTransition($choice2->entry);
        $choice1->exit->addTransition($exit);
        $choice2->exit->addTransition($exit);

        /*$this->collect(
            [
                'entry'      => serialize($entry),
                'transition' => 'ε',
                'exit'       => serialize($exit)
            ]
        );*/

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    public static function fromRegexTree(TreeTypes\Regex $regexTree)
    {
        if ($regexTree instanceof TreeTypes\Choice) {
            return FiniteAutomatonBuilder::or(
                self::fromRegexTree($regexTree->getLeft()),
                self::fromRegexTree($regexTree->getRight())
            );

        } else if ($regexTree instanceof TreeTypes\Repetition) {
            return FiniteAutomatonBuilder::rep(self::fromRegexTree($regexTree->getInternal()));
        } else if ($regexTree instanceof TreeTypes\RepetitionFromOne) {
            return FiniteAutomatonBuilder::s(
                self::fromRegexTree($regexTree->getInternal()),
                FiniteAutomatonBuilder::rep(self::fromRegexTree($regexTree->getInternal()))
            );
        } else if ($regexTree instanceof TreeTypes\Optional) {
            return FiniteAutomatonBuilder::or(
                self::fromRegexTree($regexTree->getInternal()),
                FiniteAutomatonBuilder::e()
            );
        } else if ($regexTree instanceof TreeTypes\Sequence) {
            return FiniteAutomatonBuilder::s(
                self::fromRegexTree($regexTree->getFirst()),
                self::fromRegexTree($regexTree->getSecond())
            );
        } else if ($regexTree instanceof TreeTypes\GroupItems) {
            $items = $regexTree->getItems();
            $faBuilder = self::fromRegexTree($items[0]);

            for ($i = 1; $i < count($items); $i++) {
                $faBuilder = FiniteAutomatonBuilder::or(
                    $faBuilder,
                    self::fromRegexTree($items[$i])
                );
            }

            return $faBuilder;
        } else if ($regexTree instanceof TreeTypes\Range) {
            $a = $regexTree->getA();
            $b = $regexTree->getB();

            if (ord($a) > ord($b)) {
                throw new Exception('Regular expression contains an invalid range.');
            }

            return FiniteAutomatonBuilder::c("[$a-$b]");
        } else if ($regexTree instanceof TreeTypes\Primitive) {
            return FiniteAutomatonBuilder::c($regexTree->getChar()); //new Char(((Primitive) regexTree).getChar()));
        } else if ($regexTree instanceof TreeTypes\AnyChar) {
            return FiniteAutomatonBuilder::c('[ANY]');
        } else {
            return FiniteAutomatonBuilder::e();
        }
    }

    public function getFiniteAutomaton()
    {
        return new FiniteAutomaton($this->entry);
    }
}
