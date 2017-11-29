<?php

namespace App\Infrastructure\Exceptions\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class ConflictHttpExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Exception $e, array $reporterResponses)
    {
        $response->setStatusCode(409);
        $message = json_decode($e->getMessage(), true);

        $response->setData([
            'errors' => [[
                'status' => 409,
                'code' => $e->getCode(),
                'title' => 'Resource conflict',
                'detail' => is_null($message) ? $e->getMessage() : $message
            ]]
        ]);

        return $response;
    }
}
