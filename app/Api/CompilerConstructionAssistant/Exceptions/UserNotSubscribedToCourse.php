<?php

namespace App\Api\CompilerConstructionAssistant\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotSubscribedToCourse extends NotFoundHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The user is not subscribed to the course.' : $message);
    }
}
