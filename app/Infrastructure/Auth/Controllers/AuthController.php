<?php

namespace App\Infrastructure\Auth\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Auth\LoginProxy;
use App\Infrastructure\Auth\Requests\LoginRequest;
use App\Infrastructure\Http\Controller;

class AuthController extends Controller
{
    public function isAuthenticated()
    {
        return $this->response(null, 200);
    }

    public function getUserData()
    {
        return $this->response($this->user());
    }
}
