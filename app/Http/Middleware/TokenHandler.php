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

                $refreshRequest = Request::create('oauth/token','POST',
                    [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ]);



                $service = new InternalRequest(app());

                try {
                    $resp = $service->request('POST', '/oauth/token', [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'][0],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ]);

                    /*$resp = $service->request('GET', '/api/test', [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ]);*/
                } catch (FailedInternalRequestException $e) {

                    return response()->json($e->getResponse()->content());
                }
                //return response()->json($refreshRequest->all());
                //return response()->json($refreshRequest->input('grant_type'));
                /*$request->headers->set('grant_type','password');
                $request->headers->set('refresh_token',$headers['refresh-token']);
                $request->headers->set('client_id','2');
                $request->headers->set('client_secret','YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G');
                $request->headers->set('scope','*');*/

                //$response = $client->get("www.google.com");
                //return response()->json($headersa);

                //$response = Route::dispatch($refreshRequest);
                return $resp;
                //return $response;
                return $response;
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
