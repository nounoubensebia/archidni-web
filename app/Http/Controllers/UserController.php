<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Emails\MailSender;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\RefreshToken;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;
use Matthewbdaly\LaravelInternalRequests\Exceptions\FailedInternalRequestException;
use Matthewbdaly\LaravelInternalRequests\Services\InternalRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;



use DateTime;
use GuzzleHttp\Psr7\Response;

use Laravel\Passport\Passport;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class UserController extends Controller
{

    public function signup (Request $request)
    {
        $user = new User(['email'=>$request->input('email'),
            'password' => bcrypt($request->input('password')),'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),'connected' => 1,'verification_code'=> null,
            'verification_code_created'=>null,'email_verified' => 0]);
        $found = User::query()->where('email',$request->input('email'))->get();
        if(count($found)==0)
        {
            $user->save();
            try {
                $tokens = $this->getTokens($request->input('email'), $request->input('password'));
                $response = [
                    'msg' => 'user_created',
                    'user' => $user,
                    'tokens' => $tokens
                ];
                $mailSender = new MailSender();
                $verifCode = $mailSender->sendVerificationCode($user);
                $user->verification_code = $verifCode;
                $user->verification_code_created = Carbon::now()->toDateTimeString();
                $user->save();
                return response()->json($response,201);
            } catch (FailedInternalRequestException $e) {
                return response()->json(["msg" => "internal server error"],500);
            }
        }
        else
        {
            $response = [
                'msg' => 'user exists'
            ];
        }
        return response()->json($response,401);
    }


    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $user->connected = 0;
        $user->save();
        $userTokens = $user->tokens;
        foreach ($userTokens as $userToken)
        {
            $userToken->revoke();
        }
        return response()->json(['msg' =>'disconnected'],200);
    }

    public function login (Request $request)
    {
        $email = $request->input('email');
        $pass = $request->input('password');

        $credentials = $request->only('email','password');
        if (Auth::once($credentials)) {
            $user = Auth::user();
            if ($user->connected == 1)
            {
                return response()->json([],403);
            }
            try {
                $tokens = $this->getTokens($email,$pass);

            } catch (FailedInternalRequestException $e) {
                return response()->json(['message' => 'internal server error'],500);
            }
            $user->connected = 1;
            $user->save();
            return response()->json(['user' => $user,'tokens' =>$tokens],200);
        }
        else
        {
            return response()->json(['msg' =>'Invalid Credentials'],401);
        }
    }

    /**
     * @param $user
     * @return array
     * @throws FailedInternalRequestException
     */
    public function getTokens ($email,$pass)
    {
        $service = new InternalRequest(app());
            $resp = $service->request('POST', '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => 'YxXYNvrTWIxTpZQaqINcGmUlIl6o6TqJziVB601G',
                'username' => $email,
                'password' => $pass,
                'scope' => '*',
            ]);
            $responseObj = json_decode($resp->getContent());
            $expiresIn = $responseObj->expires_in;
            $accessToken = $responseObj->access_token;
            $refreshToken = $responseObj->refresh_token;
            return ['expires_in' => $expiresIn,'access_token'=>$accessToken,'refresh_token'=>$refreshToken];
    }

    public function updatePassword ($id,Request $request)
    {
        $user = Auth::user();
        if ($user->id != $id)
        {
            return response()->json(["message" => "Unauthenticated"],401);
        }

        if (!Hash::check($request->input('old_password'),$user->password))
        {
            return response()->json(["message" => "old password incorrect"],408);
        }

        $user->password = bcrypt($request->input("new_password"));
        $user->save();
        $userTokens = $user->tokens;
        foreach ($userTokens as $userToken)
        {
            $userToken->revoke();
        }
        try {
            $tokens = $this->getTokens($user->email, $request->input("new_password"));
        } catch (FailedInternalRequestException $e) {
            //return response()->json(['email' => $user->email,"password" => $request->input("new_password")]);
            return response()->json(["msg" => "internal server error"],500);
        }
        return response()->json(["message" => "Password Changed","tokens" => $tokens]);
    }

    public function updateInfo ($id,Request $request)
    {
        $user = Auth::user();
        if ($user->id != $id)
        {
            return response()->json(["message" => "Unauthenticated"],401);
        }
        $firestName = $request->input("first_name");
        $lastName = $request->input("last_name");
        $user->first_name = $firestName;
        $user->last_name = $lastName;
        $user->save();
        return response()->json(["message" => "User info changed successfully"],200);
    }


}
