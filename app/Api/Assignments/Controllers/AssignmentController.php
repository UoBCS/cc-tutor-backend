<?php

namespace App\Api\Assignments\Controllers;

use App\Api\Assignments\Requests\CreateAssignmentRequest;
use App\Api\Assignments\Services\AssignmentService;
use App\Infrastructure\Http\Crud\Controller;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class AssignmentController extends Controller
{
    protected $key = 'assignment';

    protected $createRules = [
        'assignment'             => 'array|required',
        'assignment.title'       => 'required|string',
        'assignment.type'        => 'required|string',
        'assignment.description' => 'required|string',
        'assignment.due_date'    => 'required|date',
        'assignment.extra'       => 'array'
    ];

    protected $updateRules = [
        'assignment'             => 'array|required',
        'assignment.title'       => 'string',
        'assignment.type'        => 'string',
        'assignment.description' => 'string',
        'assignment.due_date'    => 'date',
        'assignment.extra'       => 'array'
    ];

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    public function getSubmissions($id)
    {
        return $this->response(
            $this->service->getSubmissions($id)
        );
    }

    public function submit($id)
    {
        $this->service->submit($id);
        return $this->response('', 204);
    }

    public function runTests($assignmentId, $studentId)
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        return $this->response(
            $this->service->runTests($assignmentId, $studentId)
        );
    }

    protected function processCreateData($data)
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        $data['teacher_id'] = $user->id;
        $data['start_date'] = Carbon::now();

        return $data;
    }

    protected function processUpdateData($data)
    {
        $user = $this->user();

        if (!$user->teacher) {
            return getOnly(['extra'], $data);
        }

        return $data;
    }

    protected function processCreationResult($assignment, $data)
    {
        // Attach assignment to students
        $this->service->attachToStudents($assignment, $data);

        return $data;
    }
}
