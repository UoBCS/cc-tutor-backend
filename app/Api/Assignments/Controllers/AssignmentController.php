<?php

namespace App\Api\Assignments\Controllers;

use App\Api\Assignments\Requests\CreateAssignmentRequest;
use App\Api\Assignments\Services\AssignmentService;
use App\Infrastructure\Http\Controller;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
// use App\Infrastructure\Http\Crud\Controller;

class AssignmentController extends Controller
{
    /*protected $key = 'assignment';

    protected $createRules = [
        'assignment' => 'array|required',
    ];

    protected $updateRules = [
        'assignment' => 'array|required',
    ];*/

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->service->getAllAssignments($this->user(), $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions);

        return $this->response($parsedData);
    }

    public function create(CreateAssignmentRequest $request)
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        $data = $request->all()['assignment'];
        $data['teacher_id'] = $user->id;

        return $this->response(
            $this->service->create($data),
            201
        );
    }
}
