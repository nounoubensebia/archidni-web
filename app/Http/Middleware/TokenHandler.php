<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Contracts\Auth\Guard;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;


use Matthewbdaly\LaravelInternalRequests\Exceptions\FailedInternalRequestException;
use Matthewbdaly\LaravelInternalRequests\Services;

use Matthewbdaly\LaravelInternalRequests\Services\InternalRequest;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;
use Illuminate\Contracts\Auth\Factory as Auth;

use DeepCopy\DeepCopy;

class TokenHandler
{


    private $minVersion = 0;

    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;


    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {

        $headers = $request->headers->all();
        if (isset($headers['app-version']))
        {
            if ($this->minVersion > $headers['app-version'][0])
            {
                return response()->json(['message' => 'incorrect app version'],410);
            }
        }

        if (isset($headers['authorization']))
        {
            try {
                $this->authenticate($guards);
            } catch (AuthenticationException $authenticationException)
            {
                return response()->json(['message' => 'Unauthenticated'],401);
            }
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated'],401);
    }

    protected function authenticate(array $guards)
    {
        if (empty($guards)) {
            return $this->auth->authenticate();
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
