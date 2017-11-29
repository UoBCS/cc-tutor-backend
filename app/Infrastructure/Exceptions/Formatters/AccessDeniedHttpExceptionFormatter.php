<?php

namespace App\Infrastructure\Exceptions\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class AccessDeniedHttpExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Exception $e, array $reporterResponses)
    {
        $response->setStatusCode(403);
        $message = json_decode($e->getMessage(), true);

        $response->setData([
            'errors' => [[
                'status' => 403,
                'code' => $e->getCode(),
                'title' => 'Access denied',
                'detail' => is_null($message) ? $e->getMessage() : $message
            ]]
        ]);

        return $response;
    }
}
