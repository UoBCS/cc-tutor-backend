<?php

namespace App\Api\Assignments\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssignmentNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The assignment was not found.');
    }
}
