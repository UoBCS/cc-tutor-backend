<?php

namespace App\Api\Algorithms\Controllers;

use App\Api\Algorithms\Requests\CekMachineRunRequest;
use App\Api\Algorithms\Requests\DfaRequest;
use App\Api\Algorithms\Requests\DfaOpsMembershipRequest;
use App\Api\Algorithms\Requests\NfaRequest;
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
        $result = $this->service->regexToNfa($regex);

        return $this->response($result);
    }

    public function nfaToDfa(NfaRequest $request)
    {
        $result = $this->service->nfaToDfa($request->input('nfa'));

        return $this->response($result);
    }

    public function minimizeDfa(DfaRequest $request)
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

    public function dfaOpsMembership(DfaOpsMembershipRequest $request)
    {
        $result = $this->service->dfaOpsMembership($request->input('dfa'), $request->input('word'));

        return $this->response($result);
    }
}
