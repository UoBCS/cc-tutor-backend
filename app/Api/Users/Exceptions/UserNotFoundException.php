<?php

namespace App\Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotFoundException extends NotFoundHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The user was not found.' : $message);
    }
}
