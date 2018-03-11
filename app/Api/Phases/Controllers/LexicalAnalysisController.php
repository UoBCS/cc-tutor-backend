<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\InitLLParserRequest;
use App\Api\Phases\Requests\LexicalAnalysisRequest;
use App\Api\Phases\Services\LexicalAnalysisService;
use App\Infrastructure\Http\Controller;
use Illuminate\Http\Request;

class LexicalAnalysisController extends Controller
{
    public function __construct(LexicalAnalysisService $service)
    {
        $this->service = $service;
    }

    public function run(LexicalAnalysisRequest $request)
    {
        return $this->response(
            $this->service->run(
                $request->input('content'),
                $request->input('token_types'),
                $request->input('dfa_minimized')
            )
        );
    }
}
