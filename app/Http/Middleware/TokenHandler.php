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
        $copier = new DeepCopy(true);
        $oldRequst = $copier->copy($request);
        $oldRequst = $request->duplicate();
        //return response()->json($oldRequst->route());
        //$oldRequst = unserialize($oldRequst);
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
                if (isset($headers['authorization']))
                {
                    return response()->json(['message' => 'Unauthenticated'],401);
                }

                $refreshRequest = Request::create('oauth/token','POST',
                    [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ],[],[],['HTTP_Accept'             => 'application/json']);

                $resp = Route::dispatch($refreshRequest);
                return $resp;
                $service = new InternalRequest(app());

                try {
                    /*$resp = $service->request('POST', '/oauth/token', [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'][0],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '',
                    ]);*/

                    /*$resp = $service->request('GET', '/api/test', [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $headers['refresh-token'],
                        'client_id' => '2',
                        'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                        'scope' => '*',
                    ]);*/
                } catch (FailedInternalRequestException $e) {
                    return $next($oldRequst);
                    $str = $e->getResponse()->getContent();
                    return response($str,401);
                }

                return $next($oldRequst);
                //$content = $resp->getContent();
                //$jsonObj = json_decode($content);
                //$error = $jsonObj->error;
                //return $error;
                //return $response;
                $respObj = json_decode($resp->getContent());
                $access_token = $respObj->access_token;
                $refresh_token = $respObj->refresh_token;
                //$responsea->headers->set('access-token',$access_token);
                //$responsea->headers->set('refresh-token',$refresh_token);


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
