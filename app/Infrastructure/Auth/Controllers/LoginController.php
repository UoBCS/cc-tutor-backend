<?php

namespace App\Infrastructure\Auth\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Auth\LoginProxy;
use App\Infrastructure\Auth\Requests\LoginRequest;
use App\Infrastructure\Http\Controller;

/**
 * Controller for login routes
 */
class LoginController extends Controller
{
    /**
     * The login proxy to deal with the authentication server
     *
     * @var LoginProxy
     */
    private $loginProxy;

    /**
     * Create a new LoginController
     *
     * @param LoginProxy $loginProxy
     */
    public function __construct(LoginProxy $loginProxy)
    {
        $this->loginProxy = $loginProxy;
    }

    /**
     * Log in a user
     *
     * @param  LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        return $this->response($this->loginProxy->attemptLogin(
            $request->input('email'),
            $request->input('password')
        ));
    }

    /**
     * Refresh a token
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        return $this->response($this->loginProxy->attemptRefresh());
    }

    /**
     * Log out a user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->loginProxy->logout();

        return $this->response(null, 204);
    }
}
