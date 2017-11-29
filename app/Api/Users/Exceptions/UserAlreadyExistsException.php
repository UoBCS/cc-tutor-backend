<?php

namespace App\Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserAlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The user already exists.' : $message);
    }
}
