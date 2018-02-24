<?php

namespace App\Api\Assignments\Services;

use App\Api\Assignments\Events;
use App\Api\Assignments\Exceptions;
use App\Api\Assignments\Repositories\AssignmentRepository;
use App\Api\Users\Models\User;
use App\Infrastructure\Http\Crud\Service;
use Illuminate\Support\Facades\Auth;

class AssignmentService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\AssignmentWasCreated::class,
        'resourceWasDeleted' => Events\AssignmentWasDeleted::class,
        'resourceWasUpdated' => Events\AssignmentWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\AssignmentAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\AssignmentNotFoundException::class
    ];

    public function __construct(AssignmentRepository $repository)
    {
        $this->repository = $repository;
        $this->user       = Auth::user();
    }

    public function getAll($options = [])
    {
        $query = $this->user->assignments()->getQuery();
        return $this->repository->query($query, $options)->get();
    }

    public function getById($id, $options = [])
    {
        $resource = parent::getById($id, $options);

        // Get directory contents

        return $resource;
    }

    public function create($data)
    {
        $resource = parent::create($data);

        // Create directories

        return $resource;
    }
}
