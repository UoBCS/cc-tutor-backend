<?php

namespace App\Api\Assignments\Services;

use App\Api\Assignments\Events;
use App\Api\Assignments\Exceptions;
use App\Api\Assignments\Repositories\AssignmentRepository;
use App\Infrastructure\Http\Crud\Service;
use Illuminate\Support\Facades\Auth;

class AssignmentService extends Service
{
    protected $events = [
        'resourceWillBeDeleted' => Events\AssignmentWillBeDeleted::class,
        'resourceWasCreated'    => Events\AssignmentWasCreated::class,
        'resourceWasDeleted'    => Events\AssignmentWasDeleted::class,
        'resourceWasUpdated'    => Events\AssignmentWasUpdated::class
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
        $data = [];

        $data['assignment'] = parent::getById($id, $options);
        $data['contents']   = $this->repository->getAssignmentContents($this->user, $data['assignment']);

        return $data;
    }

    public function create($data)
    {
        $dataCopy = $data;
        unset($dataCopy['extra']);
        $resource = parent::create($dataCopy);

        // 'impl_general', 'regex_to_nfa', 'nfa_to_dfa', 'll', 'lr', 'll1', 'lr0', 'cek_machine'
        // Create directories
        $this->repository->createTeacherTestDirectory($data, $this->user);

        return $resource;
    }

    public function attachToStudents($assignment, $data)
    {
        $students = $this->user->users()->get();

        $assignment->students()->attach($students->map(function ($student) {
            return $student->id;
        }));

        $this->repository->createStudentSolutionsDirectories($data, $students);

        return $assignment;
    }
}
