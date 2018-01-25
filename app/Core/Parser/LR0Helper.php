<?php

namespace App\Core\Parser;

use App\Core\Automata\State;
use App\Core\Exceptions\ParserException;
use App\Core\Syntax\Grammar\Grammar;
use App\Core\Syntax\Grammar\GrammarEntity;
use Ds\Set;
use Ds\Stack;
use Exception;

class LR0Helper
{
    public static function itemClosure(LRItem $item, Grammar $grammar) : Set
    {
        $closure = new Set([$item]);
        $stack = new Stack([$item]);
        $visitedGrammarEntities = new Set();

        while (!$stack->isEmpty()) {
            try {
                $item = $stack->pop();
                $grammarEntity = $item->getNext();

                if (!$visitedGrammarEntities->contains($grammarEntity) && $grammarEntity->isNonTerminal()) {
                    foreach ($grammar->getProductions($grammarEntity) as $rhs) {
                        $newItem = new LRItem($grammarEntity, $rhs);
                        $closure->add($newItem);
                        $stack->push($newItem);
                    }

                    $visitedGrammarEntities->add($grammarEntity);
                }
            } catch (ParserException $e) {}
        }

        return $closure;
    }

    public static function getTransitions(State $state) : Set
    {
        $transitions = new Set();
        $items = $state->getData();

        foreach ($items as $item) {
            try {
                $transitions->add($item->getNext());
            } catch (ParserException $e) {}
        }

        return $transitions;
    }

    public static function setItems(State $destState, State $sourceState, GrammarEntity $grammarEntity, Grammar $grammar)
    {
        $destStateItems = new Set();
        $items = $sourceState->getData();

        foreach ($items as $lrItem) {
            try {
                if ($lrItem->getNext()->equals($grammarEntity)) {
                    $destLrItem = clone $lrItem;
                    $destLrItem->advanceDot();

                    $destStateItems->add($destLrItem);
                }
            } catch (ParserException $e) {}
        }

        while (true) {
            $previousCount = count($destStateItems->toArray());
            $destStateItemsHelper = new Set();

            foreach ($destStateItems as $lrItem) {
                try {
                    if ($lrItem->getNext()->isNonTerminal()) {
                        $destStateItemsHelper = $destStateItemsHelper->merge(self::itemClosure($lrItem, $grammar));
                    }
                } catch (ParserException $e) {}
            }

            $destStateItems = $destStateItems->merge($destStateItemsHelper);

            if ($previousCount === count($destStateItems->toArray())) {
                break;
            }
        }

        //var_dump($destStateItems);
        $destState->setData($destStateItems);
    }

    public static function setFinal(State $state)
    {
        $items = $state->getData();

        foreach ($items as $lrItem) {
            if ($lrItem->dotIsAtTheEnd()) {
                $state->setFinal();
                break;
            }
        }
    }

    public static function equalStates($state1, $state2)
    {
        $items1 = $state1->getData();
        $items2 = $state2->getData();

        if (count($items1->toArray()) !== count($items2->toArray())) {
            return false;
        }

        foreach ($items1 as $item) {
            if (!$items2->contains($item)) {
                return false;
            }
        }

        return true;
    }
}
