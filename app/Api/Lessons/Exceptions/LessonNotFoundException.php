<?php

namespace App\Api\Lessons\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LessonNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The lesson was not found.');
    }
}
