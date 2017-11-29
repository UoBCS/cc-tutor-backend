<?php

namespace App\Infrastructure\Exceptions\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;
use Optimus\Heimdal\Formatters\BaseFormatter;

class NotFoundHttpExceptionFormatter extends BaseFormatter
{
    public function format(JsonResponse $response, Exception $e, array $reporterResponses)
    {
        $response->setStatusCode(404);
        $message = json_decode($e->getMessage(), true);

        $response->setData([
            'errors' => [[
                'status' => 404,
                'code' => $e->getCode(),
                'title' => 'Not found',
                'detail' => is_null($message) ? $e->getMessage() : $message
            ]]
        ]);

        return $response;
    }
}
