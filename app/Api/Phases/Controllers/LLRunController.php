<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\LexicalAnalysisRequest;
use App\Api\Phases\Requests\LLRunMatchRequest;
use App\Api\Phases\Requests\LLRunPredictRequest;
use App\Api\Phases\Services\LLRunService;
use App\Core\Lexer\Lexer;
use App\Core\Parser\LL;
use App\Infrastructure\Http\Crud\Controller;
use Illuminate\Http\Request;

class LLRunController extends Controller
{
    protected $key = 'll_run';

    protected $createRules = [
        'll_run'             => 'array|required',
        'll_run.content'     => 'required|string',
        'll_run.token_types' => 'required|token_types',
        'll_run.grammar'     => 'required|grammar'
    ];

    public function __construct(LLRunService $service)
    {
        $this->service = $service;
    }

    public function predict(LLRunPredictRequest $request)
    {
        return $this->response(
            $this->service->predict(
                $request->input('run_id'),
                $request->input('lhs'),
                $request->input('rhs')
            )
        );
    }

    public function match(LLRunMatchRequest $request)
    {
        return $this->response(
            $this->service->match($request->input('run_id'))
        );
    }

    protected function processCreateData($data)
    {
        return $this->service->initialize($data);
    }

    protected function processCreationResult($data)
    {
        $newData = (array) $data;

        $tokenTypes = json_decode($data['token_types'], true);
        $grammar = json_decode($data['grammar'], true);
        $lexer = new Lexer($data->content, $tokenTypes);
        $parser = new LL($lexer, $grammar);
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
