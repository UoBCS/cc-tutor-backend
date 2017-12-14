<?php

namespace App\Api\{{ pluralCapitalized }}\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class {{ singularCapitalized }}AlreadyExistsException extends ConflictHttpException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? 'The {{ singular }} already exists.' : $message);
    }
}
