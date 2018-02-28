<?php

namespace App\Api\Assignments\Services;

use App\Api\Assignments\Events;
use App\Api\Assignments\Exceptions;
use App\Api\Assignments\Repositories\AssignmentRepository;
use App\Api\Users\Exceptions\UserNotFoundException;
use App\Api\Users\Repositories\UserRepository;
use App\Infrastructure\Http\Crud\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

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

    public function __construct(AssignmentRepository $repository, UserRepository $userRepository)
    {
        $this->repository     = $repository;
        $this->userRepository = $userRepository;
        $this->user           = Auth::user();
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

        if ($this->user->teacher && $this->user->id !== $data['assignment']->teacher_id) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

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

    public function getSubmissions($id)
    {
        if (!$this->user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        $assignment = $this->getById($id)['assignment'];
        $dueDate = new Carbon($assignment->due_date);
        $overdue = $dueDate->diffInMinutes(Carbon::now(), false) > 0;
        $submissions = [];

        foreach ($assignment->students as $student) {
            $submission = [
                'student'   => $student,
                'submitted' => true,
                'late'      => false
            ];

            $submittedDate = $student->pivot->submission_date;

            if ($submittedDate === null) {
                $submission['submitted'] = false;

                if ($overdue) {
                    $submission['late'] = true;
                    $submissions[] = $submission;
                }
                continue;
            }

            $submittedDate = new Carbon($student->pivot->submission_date);

            if ($dueDate->diffInMinutes($submittedDate, false) > 0) {
                $submission['late'] = true;
            }

            $submissions[] = $submission;
        }

        return $submissions;
    }

    public function submit($id)
    {
        $assignment = $this->getById($id)['assignment'];

        $assignment->students()->updateExistingPivot($this->user->id, [
            'submission_date' => Carbon::now()
        ]);
    }

    public function runTests($assignmentId, $studentId)
    {
        $assignment = $this->getById($assignmentId)['assignment'];
        $student    = $this->userRepository->getById($studentId);

        if ($student === null) {
            throw new UserNotFoundException();
        }

        if (!$assignment->students->contains($studentId)) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        $this->repository->copySolutionToTeacher($assignment, $student, $this->user);

        $username = normalizeName($this->user->name);
        $title    = normalizeName($assignment->title);

        $currentDir = getcwd();
        chdir(storage_path('app/cctutor'));
        [$output, $exitCode] = mvnCompile($this->repository->getFullPath($username, $title));
        [$output, $exitCode] = mvnTest("com.cctutor.app.$username.assignments.$title.**");
        chdir($currentDir);

        return [
            'output'    => $output,
            'exit_code' => $exitCode
        ];
    }
}
