<?php

namespace App\Api\Phases\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NonDetParserRunNotFoundException extends NotFoundHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The non-deterministic parser run was not found.' : $message);
    }
}
