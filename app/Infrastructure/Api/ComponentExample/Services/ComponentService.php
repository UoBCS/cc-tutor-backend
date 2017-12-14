<?php

namespace App\Api\{{ pluralCapitalized }}\Services;

use App\Api\{{ pluralCapitalized }}\Events;
use App\Api\{{ pluralCapitalized }}\Exceptions;
use App\Api\{{ pluralCapitalized }}\Repositories\{{ singularCapitalized }}Repository;
use App\Infrastructure\Http\Crud\Service;

class {{ singularCapitalized }}Service extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\{{ singularCapitalized }}WasCreated::class,
        'resourceWasDeleted' => Events\{{ singularCapitalized }}WasDeleted::class,
        'resourceWasUpdated' => Events\{{ singularCapitalized }}WasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\{{ singularCapitalized }}AlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\{{ singularCapitalized }}NotFoundException::class
    ];

    public function __construct({{ singularCapitalized }}Repository $repository)
    {
        $this->repository = $repository;
    }
}
