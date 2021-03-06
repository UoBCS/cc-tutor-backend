<?php

namespace App\Api\Users\Controllers;

use App\Api\Users\Requests\SendClassInvitationEmailRequest;
use App\Api\Users\Services\UserService;
use App\Infrastructure\Http\Controller;
use App\Infrastructure\Http\Validation\SimpleValidationTrait;
use App\Infrastructure\Jobs\SendClassInvitationEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class UserController extends Controller
{
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function getStudents()
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        return $user->users()->get();
    }

    public function submitClassInvitation($token)
    {
        return $this->service->submitClassInvitation($this->user(), $token);
    }

    public function getTeachers()
    {
        $user = $this->user();

        if ($user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        return $user->users()->get();
    }

    public function sendClassInvitationEmail(SendClassInvitationEmailRequest $request)
    {
        $user = $this->user();

        if (!$user->teacher) {
            throw new SymfonyException\AccessDeniedHttpException();
        }

        dispatch(new SendClassInvitationEmail($user, $request->input('emails')));

        return response(200);
    }
}
