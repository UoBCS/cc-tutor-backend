<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\LR0ParseRequest;
use App\Api\Phases\Services\LR0Service;
use App\Infrastructure\Http\Controller;

class LR0Controller extends Controller
{
    public function __construct(LR0Service $service)
    {
        $this->service = $service;
    }

    public function parse(LR0ParseRequest $request)
    {
        return $this->response(
            $this->service->parse(
                $request->input('content'),
                $request->input('token_types'),
                $request->input('grammar'),
                $request->input('interactive', true)
            )
        );
    }
}
