<?php

namespace App\Http\Controllers;

use App\Bus;
use App\Http\Controllers\RealTimeBuses\BusUpdater;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class BusController extends Controller
{
    //
    public function updateLocations (Request $request)
    {
        $busUpdater = new BusUpdater();
        try {
            $resp = $busUpdater->updateLocations();
        } catch (\Exception $e) {
            if ($e instanceof GuzzleException)
                return response()->json(['msg' => 'error connecting to real time server'],500);
            else
                return response()->json(['msg' => 'update not authorized by real time server'],500);
        }
        return response()->json($resp);
    }

    public function index ()
    {
        return Bus::all();
    }
}
