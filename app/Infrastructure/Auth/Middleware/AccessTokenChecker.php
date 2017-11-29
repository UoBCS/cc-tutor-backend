<?php

namespace App\Infrastructure\Auth\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Middleware to check for validity of the passed access token
 */
class AccessTokenChecker
{
    private $oAuthMiddleware;

    /**
     * Create a new AccessTokenChecker
     *
     * @param Authenticate $authenticate
     */
    public function __construct(Authenticate $authenticate) {
        $this->authenticate = $authenticate;
    }

    /**
     * Handle the middleware
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure                   $next
     * @param  string                    $scopesString
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next, $scopesString = null)
    {
        if (app()->environment() !== 'testing') {
            try {
                return $this->authenticate->handle($request, $next, 'api');
            } catch (AuthenticationException $e) {
                throw new UnauthorizedHttpException('Challenge');
            }
        }

        return $next($request);
    }
}
