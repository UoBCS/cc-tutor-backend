<?php

namespace App\Api\Algorithms\Controllers;

use App\Api\Algorithms\Requests\CekMachineRunRequest;
use App\Api\Algorithms\Requests\MinimizeDfaRequest;
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

    public function regexToNfa($regex)
    {
        // TODO: Regex validation

        $result = $this->service->regexToNfa($regex);

        return $this->response($result);
    }

    public function nfaToDfa(NfaToDfaRequest $request)
    {
        $result = $this->service->nfaToDfa($request->input('nfa'));

        return $this->response($result);
    }

    public function minimizeDfa(MinimizeDfaRequest $request)
    {
        $result = $this->service->minimizeDfa($request->input('dfa'));

        return $this->response($result);
    }

    public function cekMachineNextStep(CekMachineRunRequest $request)
    {
        $result = $this->service->cekMachineNextStep($request->input('cek_machine'));

        return $this->response($result);
    }

    public function cekMachineRun(CekMachineRunRequest $request)
    {
        $result = $this->service->cekMachineRun($request->input('cek_machine'));

        return $this->response($result);
    }
}
