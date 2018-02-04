<?php

namespace App\Api\CompilerConstructionAssistant\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LessonNotFoundException extends NotFoundHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The lesson was not found.' : $message);
    }
}
