<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class UserController extends Controller
{
    //



    public function signup (Request $request)
    {
        $user = new User(['email'=>$request->input('email'),
            'password' => bcrypt($request->input('password')),'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name')]);
        $found = User::query()->where('email',$request->input('email'))->get();
        if(count($found)==0)
        {
            $user->save();
            $response = [
                'msg' => 'user_created',
                'user' => $user
            ];
            return response()->json($response,201);
        }
        else
        {
            $response = [
                'msg' => 'user exists'
            ];
        }
        return response()->json($response,401);
    }

    public function login (Request $request)
    {
        $email = $request->input('email');
        $pass = $request->input('password');

        /*$user = User::query()->where('email',$email)->get();
        //return $user->first()->password;
        if ($user->count()==0||!Hash::check($pass,$user->first()->password))
        {
            $response = [
                'msg' => 'error, email or password incorrect'
            ];
            return response()->json($response,401);
        }
        else
        {
            $response = [
                'msg' =>'user_logged_in',
                'user' => $user->first()
            ];
            return response()->json($response,200);
        }*/
        $credentials = $request->only('email','password');
        try {
            if (!$token = JWTAuth::attempt($credentials))
            {
                return response()->json(['msg' =>'Invalid Credentials'],401);
            }

        } catch (JWTException $JWTException)
        {
            return response()->json(['msg' => 'Could not create token']);
        }

        return response()->json(['token' => $token]);
    }

}
