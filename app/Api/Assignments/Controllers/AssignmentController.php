<?php

namespace App\Api\Assignments\Controllers;

use App\Api\Assignments\Requests\CreateAssignmentRequest;
use App\Api\Assignments\Services\AssignmentService;
use App\Infrastructure\Http\Crud\Controller;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class AssignmentController extends Controller
{
    protected $key = 'assignment';

    protected $createRules = [
        'assignment'             => 'array|required',
        'assignment.title'       => 'required|string',
        'assignment.type'        => 'required|string',
        'assignment.description' => 'required|string'
    ];

    protected $updateRules = [
        'assignment'             => 'array|required',
        'assignment.title'       => 'string',
        'assignment.type'        => 'string',
        'assignment.description' => 'string'
    ];

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    protected function processCreateData($data)
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        $data['teacher_id'] = $user->id;

        return $data;
    }
}
