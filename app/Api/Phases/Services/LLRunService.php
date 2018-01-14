<?php

namespace App\Api\Phases\Services;

use App\Api\Phases\Events;
use App\Api\Phases\Exceptions;
use App\Api\Phases\Repositories\LLRunRepository;
use App\Core\Inspector;
use App\Core\IO\InputStream;
use App\Core\Lexer\Lexer;
use App\Core\Parser\LL;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Http\Crud\Service;
use Ds\Vector;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class LLRunService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\LLRunWasCreated::class,
        'resourceWasDeleted' => Events\LLRunWasDeleted::class,
        'resourceWasUpdated' => Events\LLRunWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\LLRunAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\LLRunNotFoundException::class
    ];

    private $inspector;

    public function __construct(LLRunRepository $repository)
    {
        $this->repository = $repository;
        $this->inspector = inspector();
        //$this->inspector->getState('breakpoints')
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

    public function predict(int $runId, string $lhs, array $rhs)
    {
        $llRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($llRun);

        $grammar = $parser->getGrammar();
        $rhsV = new Vector();

        foreach ($rhs as $item) {
            $rhsV->push($grammar->getGrammarEntityByName($item));
        }

        $parser->predict(new NonTerminal($lhs), $rhsV);

        $this->updateLLRun($llRun, $parser);

        return $parser->jsonSerialize();
    }

    public function match(int $runId)
    {
        $llRun = $this->getRequestedResource($runId);

        $parser = $this->createParser($llRun);

        $parser->match();

        $this->updateLLRun($llRun, $parser);

        return $parser->jsonSerialize();
    }

    private function createParser($llRun)
    {
        return LL::fromData([
            'content'    => $llRun->content,
            'token_types' => json_decode($llRun->token_types, true),
            'grammar'    => json_decode($llRun->grammar, true),
            'stack'      => is_null($llRun->stack) ? null : json_decode($llRun->stack, true),
            'input_index' => $llRun->input_index,
            'parse_tree' => json_decode($llRun->parse_tree, true)
        ]);
    }

    private function updateLLRun($llRun, $parser)
    {
        $parserJson = $parser->jsonSerialize();

        $this->repository->update($llRun, [
            'stack'       => json_encode($parser->getStack()->toArray()),
            'input_index' => $parser->getInput()->getIndex(),
            'parse_tree'  => json_encode($parserJson['parse_tree'])
        ]);
    }
}
