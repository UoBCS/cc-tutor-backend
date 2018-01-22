<?php

namespace App\Api\Phases\Controllers;

use App\Api\Phases\Requests\LL1ParseRequest;
use App\Api\Phases\Services\LL1Service;
use App\Infrastructure\Http\Controller;

class LL1Controller extends Controller
{
    public function __construct(LL1Service $service)
    {
        $this->service = $service;
    }

    public function parse(LL1ParseRequest $request)
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

    public function first()
    {

    }

    public function follow()
    {

    }
}
