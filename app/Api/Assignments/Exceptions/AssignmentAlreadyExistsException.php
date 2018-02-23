<?php

namespace App\Api\Assignments\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AssignmentAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The assignment already exists.' : $message);
    }
}
