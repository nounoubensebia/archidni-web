<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Matthewbdaly\LaravelInternalRequests\Exceptions\FailedInternalRequestException;

class AdminController extends Controller
{
    //
    public function login(Request $request)
    {
        $email = $request->input('email');
        $pass = $request->input('password');

        $credentials = $request->only('email','password');

        if (Auth::once($credentials)) {
            $user = Auth::user();
            if ($user->is_admin!=1)
            {
                return response()->json(['msg' =>'Invalid Credentials'],401);
            }
            try {
                $userController = new UserController();
                $tokens = $userController->getTokens($email,$pass);
            } catch (FailedInternalRequestException $e) {
                return response()->json(['message' => 'internal server error'],500);
            }
            return response()->json(['user' => $user,'tokens' =>$tokens],200);
        }
        else
        {
            return response()->json(['msg' =>'Invalid Credentials'],401);
        }

    }
}
