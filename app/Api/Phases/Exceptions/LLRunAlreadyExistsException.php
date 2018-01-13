<?php

namespace App\Api\Phases\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class LLRunAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The LL run already exists.' : $message);
    }
}
