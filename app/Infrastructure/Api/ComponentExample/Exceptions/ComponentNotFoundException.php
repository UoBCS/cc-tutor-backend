<?php

namespace App\Api\{{ pluralCapitalized }}\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class {{ singularCapitalized }}NotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The {{ singular }} was not found.');
    }
}
