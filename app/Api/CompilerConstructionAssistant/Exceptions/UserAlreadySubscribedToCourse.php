<?php

namespace App\Api\CompilerConstructionAssistant\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserAlreadySubscribedToCourse extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The user is already subscribed to the course.' : $message);
    }
}
