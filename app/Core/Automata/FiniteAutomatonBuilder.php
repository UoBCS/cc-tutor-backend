<?php

namespace App\Core\Automata;

use App\Core\Inspector;
use App\Core\Syntax\Regex\TreeTypes;
use Exception;

/**
 * Helper class to build a finite automaton from regular expressions
 */
class FiniteAutomatonBuilder
{
    public $entry;
    public $exit;
    private static $inspector;

    /**
     * Create a new finite automaton builder
     *
     * @param State $entry The entry state
     * @param State $exit  The exit state
     */
    public function __construct(State $entry, State $exit)
    {
        $this->entry = $entry;
        $this->exit = $exit;
    }

    /**
     * Initializes the breakpoints storage
     *
     * @return void
     */
    public static function init()
    {
        self::$inspector = inspector();
        self::$inspector->createStore('breakpoints', 'array');
    }

    /**
     * Creates a character fragment
     *
     * @param string $c
     * @return self
     */
    public static function c(string $c) : self
    {
        $entry = new State();
        $exit = new State();
        $exit->setFinal();
        $entry->addTransition($exit, [$c]);

        /* > */ self::$inspector->breakpoint('c', [
        /* > */    'entry'      => $entry,
        /* > */    'transition' => $c,
        /* > */    'exit'       => $exit
        /* > */ ]);

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    /**
     * Creates an epsilon fragment
     *
     * @return self
     */
    public static function e() : self
    {
        $entry  = new State();
        $exit = new State();
        $entry->addTransition($exit);
        $exit->setFinal();

        /* > */ self::$inspector->breakpoint('e', [
        /* > */    'entry'      => $entry,
        /* > */    'transition' => 'ε',
        /* > */    'exit'       => $exit
        /* > */ ]);

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    /**
     * Creates a repetition fragment
     *
     * @param self $nfa
     * @return self
     */
    public static function rep(self $nfa) : self
    {
        $nfa->exit->addTransition($nfa->entry);
        $nfa->entry->addTransition($nfa->exit);

        /* > */ self::$inspector->breakpoint('rep', [
        /* > */    'state1' => $nfa->entry,
        /* > */    'state2' => $nfa->exit
        /* > */ ]);

        return $nfa;
    }

    /**
     * Creates a sequence fragment
     *
     * @param self $first
     * @param self $second
     * @return self
     */
    public static function s(self $first, self $second) : self
    {
        $first->exit->setFinal(false);
        $second->exit->setFinal();
        $first->exit->addTransition($second->entry);

        /* > */ self::$inspector->breakpoint('s', [
        /* > */    'entry'      => $first->exit,
        /* > */    'transition' => 'ε',
        /* > */    'exit'       => $second->entry
        /* > */ ]);

        return new FiniteAutomatonBuilder($first->entry, $second->exit);
    }

    /**
     * Creates a choice fragment
     *
     * @param self $choice1
     * @param self $choice2
     * @return self
     */
    public static function or(self $choice1, self $choice2) : self
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

        /* > */ self::$inspector->breakpoint('or1', [
        /* > */    'entry' => $entry
        /* > */ ]);

        /* > */ self::$inspector->breakpoint('or2', [
        /* > */    'entry'   => $entry,
        /* > */    'choices' => [$choice1->entry, $choice2->entry]
        /* > */ ]);

        /* > */ self::$inspector->breakpoint('or3', [
        /* > */    'exit' => $exit
        /* > */ ]);

        /* > */ self::$inspector->breakpoint('or4', [
        /* > */    'exit'    => $exit,
        /* > */    'choices' => [$choice1->exit, $choice2->exit]
        /* > */ ]);

        return new FiniteAutomatonBuilder($entry, $exit);
    }

    /**
     * Creates a finite automaton from a regular expression parse tree
     *
     * @param TreeTypes\Regex $regexTree
     * @return self
     */
    public static function fromRegexTree(TreeTypes\Regex $regexTree) : self
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
            return FiniteAutomatonBuilder::c($regexTree->getChar());
        } else if ($regexTree instanceof TreeTypes\AnyChar) {
            return FiniteAutomatonBuilder::c('[ANY]');
        } else {
            return FiniteAutomatonBuilder::e();
        }
    }

    /**
     * Returns the actual finite automaton
     *
     * @return FiniteAutomaton
     */
    public function getFiniteAutomaton() : FiniteAutomaton
    {
        return new FiniteAutomaton($this->entry);
    }
}
