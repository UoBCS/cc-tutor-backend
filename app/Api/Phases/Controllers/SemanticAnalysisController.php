<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\AstRequest;
use App\Api\Phases\Services\SemanticAnalysisService;
use App\Infrastructure\Http\Controller;
use Illuminate\Http\Request;

class SemanticAnalysisController extends Controller
{
    public function __construct(SemanticAnalysisService $service)
    {
        $this->service = $service;
    }

    public function ast(AstRequest $request)
    {
        return $this->response(
            $this->service->ast($request->all(), $this->user())
        );
    }
}
