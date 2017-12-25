<?php

namespace App\Api\Algorithms\Controllers;

use App\Api\Algorithms\Requests\NfaToDfaRequest;
use App\Api\Algorithms\Services\AlgorithmService;
use App\Infrastructure\Http\Controller;
use Illuminate\Http\Request;

class AlgorithmController extends Controller
{
    public function __construct(AlgorithmService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->response(
            [
                'supported_algorithms' => [
                    'regex2nfa',
                    'nfa2dfa'
                ]
            ]
        );
    }

    public function regexToNfa($regex)
    {
        // Regex validation

        return $this->response($this->service->regexToNfa($regex));
    }

    public function nfaToDfa(NfaToDfaRequest $request)
    {
        return $this->response($this->service->nfaToDfa($request->input('nfa')));
    }
}
