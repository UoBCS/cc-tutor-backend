<?php

namespace App\Api\Users\Controllers;

use App\Api\Users\Services\UserService;
use App\Infrastructure\Http\Crud\Controller;
use App\Infrastructure\Http\Validation\SimpleValidationTrait;
use Illuminate\Http\Request;


class UserController extends Controller
{
    use SimpleValidationTrait;

    protected $key = 'user';

    protected $createRules = [
        'user'          => 'array|required',
        'user.email'    => 'required|email',
        'user.name'     => 'required|string',
        'user.password' => 'required|string|min:8'
    ];

    protected $updateRules = [
        'user'              => 'array|required',
        'user.email'        => 'email',
        'user.name'         => 'string',
        'user.password'     => 'string|min:8'
    ];

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}
