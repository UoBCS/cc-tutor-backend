<?php

namespace App\Api\Courses\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The course was not found.');
    }
}
