<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Services\PhaseService;
use App\Infrastructure\Http\Controller;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function __construct(PhaseService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->response(
            [
                'supported_phases' => [
                    'Lexical Analysis',
                    'LL parsing (non-deterministic)',
                    'LR parsing (non-deterministic)',
                    'LL(1) parsing',
                    'LR(0) parsing',
                    'Type Checking',
                ]
            ]
        );
    }

    public function lexicalAnalysis()
    {

    }
}
