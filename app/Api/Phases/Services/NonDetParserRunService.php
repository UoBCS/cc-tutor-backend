<?php

namespace App\Api\Phases\Services;

use App\Api\Phases\Events;
use App\Api\Phases\Exceptions;
use App\Api\Phases\Repositories\NonDetParserRunRepository;
use App\Core\Parser\LL;
use App\Core\Parser\LR;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Infrastructure\Http\Crud\Service;

class NonDetParserRunService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\NonDetParserRunWasCreated::class,
        'resourceWasDeleted' => Events\NonDetParserRunWasDeleted::class,
        'resourceWasUpdated' => Events\NonDetParserRunWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\NonDetParserRunAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\NonDetParserRunNotFoundException::class
    ];

    public function __construct(NonDetParserRunRepository $repository)
    {
        $this->repository = $repository;
    }

    public function initialize(array $data) : array
    {
        return [
            'content'     => $data['content'],
            'token_types' => json_encode($data['token_types']),
            'grammar'     => json_encode($data['grammar']),
            'stack'       => null,
            'input_index' => 0,
            'parse_tree'  => null
        ];
    }

    public function predict(int $runId, string $lhs, $rhs)
    {
        $llRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($llRun, LL::class);

        $grammar = $parser->getGrammar();
        $rhsArr = [];

        if (!Terminal::isEpsilonStruct($rhs)) { //$rhs !== null) {
            foreach ($rhs as $item) {
                $rhsArr[] = $grammar->getGrammarEntityByName($item);
            }
        }

        $parser->predict(new NonTerminal($lhs), $rhsArr);

        $this->updateNonDetParserRun($llRun, $parser);

        return $parser->jsonSerialize();
    }

    public function match(int $runId)
    {
        $llRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($llRun, LL::class);

        $parser->match();

        $this->updateNonDetParserRun($llRun, $parser);

        return $parser->jsonSerialize();
    }

    public function reduce(int $runId, string $lhs, $rhs)
    {
        $lrRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($lrRun, LR::class);

        $grammar = $parser->getGrammar();
        $rhsArr = [];

        if (!Terminal::isEpsilonStruct($rhs)) { //$rhs !== null) {
            foreach ($rhs as $item) {
                $rhsArr[] = $grammar->getGrammarEntityByName($item);
            }
        }

        $parser->reduce(new NonTerminal($lhs), $rhsArr);

        $this->updateNonDetParserRun($lrRun, $parser);

        return $parser->jsonSerialize();
    }

    public function shift(int $runId)
    {
        $lrRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($lrRun, LR::class);

        $parser->shift();

        $this->updateNonDetParserRun($lrRun, $parser);

        return $parser->jsonSerialize();
    }

    private function createParser($ndpRun, $className)
    {
        return call_user_func("$className::fromData", [
            'content'     => $ndpRun->content,
            'token_types' => json_decode($ndpRun->token_types, true),
            'grammar'     => json_decode($ndpRun->grammar, true),
            'stack'       => is_null($ndpRun->stack) ? null : json_decode($ndpRun->stack, true),
            'input_index' => $ndpRun->input_index,
            'parse_tree'  => json_decode($ndpRun->parse_tree, true)
        ]);
    }

    private function updateNonDetParserRun($nonDetParserRun, $parser)
    {
        $parserJson = $parser->dbJsonSerialize();

        $this->repository->update($nonDetParserRun, [
            'stack'       => json_encode($parserJson['stack']),
            'input_index' => $parser->getInput()->getIndex(),
            'parse_tree'  => json_encode($parserJson['parse_tree'])
        ]);
    }
}
