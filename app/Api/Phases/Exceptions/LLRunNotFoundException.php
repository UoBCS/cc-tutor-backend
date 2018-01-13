<?php

namespace App\Api\Phases\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LLRunNotFoundException extends NotFoundHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The LL run was not found.' : $message);
    }
}
