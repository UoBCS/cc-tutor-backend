<?php

namespace App\Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StudentAlreadyAttendingClass extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The student already attends this class.' : $message);
    }
}
