<?php

namespace App\Api\Users\Services;

use App\Api\Users\Events;
use App\Api\Users\Exceptions;
use App\Api\Users\Models\User;
use App\Api\Users\Repositories\UserRepository;
use App\Infrastructure\Http\Crud\Service;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class UserService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function submitClassInvitation(User $user, string $token)
    {
        $teacher = $this->repository
                        ->scope('teachers')
                        ->where('class_invitation_token', $token)
                        ->first();

        if ($teacher === null) {
            throw new Exceptions\UserNotFoundException('Teacher not found.');
        }

        $this->repository->relateStudentAndTeacher($teacher->id);

        return $user->users()->get();
    }
}
