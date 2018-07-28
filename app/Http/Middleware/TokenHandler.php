<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Contracts\Auth\Guard;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;
use Illuminate\Contracts\Auth\Factory as Auth;

class TokenHandler
{


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
        //return response()->json($guards);
        $headers = $request->headers->all();

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
        else
        {
            if (isset($headers['refresh-token']))
            {
                $http = new Client();
                $response = $http->post('http://localhost:8000/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ],
                ]);
            }
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
