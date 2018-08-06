<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class test extends Controller
{
    //
    public function  test (Request $request)
    {
        $headers = $request->headers->all();
        return response()->json($headers,200);
    }
}
