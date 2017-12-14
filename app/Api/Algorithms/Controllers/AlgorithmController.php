<?php

namespace App\Api\Algorithms\Controllers;

use App\Api\Algorithms\Services\AlgorithmService;
use App\Infrastructure\Http\Controller;

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

    public function regexToNFA($regex)
    {
        // Regex validation

        return $this->response($this->service->regexToNFA($regex));
    }
}
