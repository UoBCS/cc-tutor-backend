<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\LexicalAnalysisRequest;
use App\Api\Phases\Requests\LLRunMatchRequest;
use App\Api\Phases\Requests\LLRunPredictRequest;
use App\Api\Phases\Services\LLRunService;
use App\Infrastructure\Http\Crud\Controller;
use Illuminate\Http\Request;

class LLRunController extends Controller
{
    protected $key = 'll_run';

    protected $createRules = [
        'll_run'             => 'array|required',
        'll_run.content'     => 'required|string',
        'll_run.token_types' => 'required|array',
        'll_run.grammar'     => 'required|array'
    ];

    protected $updateRules = [
        'content'     => 'required|string',
        'token_types' => 'required|array',
        'grammar'     => 'required|array'
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
}
