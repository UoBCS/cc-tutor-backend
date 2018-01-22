<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\NonDetParserRunRequest;
use App\Api\Phases\Requests\ProductionRequest;
use App\Api\Phases\Services\NonDetParserRunService;
use App\Core\Lexer\Lexer;
use App\Core\Parser\NonDeterministicParser;
use App\Infrastructure\Http\Crud\Controller;

class NonDetParserRunController extends Controller
{
    protected $key = 'ndp_run';

    protected $createRules = [
        'ndp_run'             => 'array|required',
        'ndp_run.content'     => 'required|string',
        'ndp_run.token_types' => 'required|token_types',
        'ndp_run.grammar'     => 'required|grammar'
    ];

    public function __construct(NonDetParserRunService $service)
    {
        $this->service = $service;
    }

    public function predict(ProductionRequest $request)
    {
        return $this->response(
            $this->service->predict(
                $request->input('run_id'),
                $request->input('lhs'),
                $request->input('rhs')
            )
        );
    }

    public function match(NonDetParserRunRequest $request)
    {
        return $this->response(
            $this->service->match($request->input('run_id'))
        );
    }

    public function reduce(ProductionRequest $request)
    {
        return $this->response(
            $this->service->reduce(
                $request->input('run_id'),
                $request->input('lhs'),
                $request->input('rhs')
            )
        );
    }

    public function shift(NonDetParserRunRequest $request)
    {
        return $this->response(
            $this->service->shift($request->input('run_id'))
        );
    }

    protected function processCreateData($data)
    {
        return $this->service->initialize($data);
    }

    protected function processCreationResult($data)
    {
        $type = request()->route()->named('ll_parsing') ? 'll' : 'lr';
        $newData = (array) $data;

        $tokenTypes = json_decode($data['token_types'], true);
        $grammar = json_decode($data['grammar'], true);
        $lexer = new Lexer($data->content, $tokenTypes);
        $parser = new NonDeterministicParser($lexer, $grammar, $type);
        $jsonParser = $parser->jsonSerialize();

        $data['token_types'] = $tokenTypes;
        $data['grammar'] = $grammar;
        $data['input'] = $jsonParser['input'];
        $data['stack'] = $jsonParser['stack'];
        $data['parse_tree'] = $jsonParser['parse_tree'];

        unset($data['input_index']);

        return $data;
    }
}
