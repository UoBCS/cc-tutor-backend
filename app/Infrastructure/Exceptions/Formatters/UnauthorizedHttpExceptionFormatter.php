<?php

namespace App\Infrastructure\Exceptions\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class UnauthorizedHttpExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Exception $e, array $reporterResponses)
    {
        $response->setStatusCode(401);

        $response->setData([
            'errors' => [[
                'status' => 401,
                'code' => $e->getCode(),
                'title' => 'Unauthorized access to endpoint',
                'detail' => $e->getMessage()
            ]]
        ]);

        return $response;
    }
}
