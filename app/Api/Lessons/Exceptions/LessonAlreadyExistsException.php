<?php

namespace App\Api\Lessons\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class LessonAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The lesson already exists.' : $message);
    }
}
