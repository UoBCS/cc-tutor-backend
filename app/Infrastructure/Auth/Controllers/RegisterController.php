<?php

namespace App\Infrastructure\Auth\Controllers;

use App\Api\Users\Models\User;
use App\Infrastructure\Auth\Requests\RegisterRequest;
use App\Infrastructure\Jobs\SendVerificationEmail;
use App\Infrastructure\Http\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

/**
 * Controller for login routes
 */
class RegisterController extends Controller
{
    protected function create(array $data)
    {
        $retData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'teacher' => $data['teacher'],
            'email_token' => base64_encode($data['email'])
        ];

        if ($data['teacher']) {
            $retData['class_invitation_token'] = str_random(16);
        }

        return User::create($retData);
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->create($request->all());
        dispatch(new SendVerificationEmail($user));

        return response(201);
    }

    public function verify($token)
    {
        $user = User::where('email_token', $token)->first();
        $user->verified = true;

        if ($user->save()) {
            return $this->response($user);
        }

        throw new SymfonyException\UnprocessableEntityHttpException('Could not verify user.');
    }
}
