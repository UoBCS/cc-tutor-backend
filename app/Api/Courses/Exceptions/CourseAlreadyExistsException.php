<?php

namespace App\Api\Courses\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CourseAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The course already exists.' : $message);
    }
}
