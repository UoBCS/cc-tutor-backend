<?php

namespace App\Api\Phases\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class NonDetParserRunAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The non-deterministic parser run already exists.' : $message);
    }
}
