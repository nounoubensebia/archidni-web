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
            $busUpdater->updateLocations();
        } catch (GuzzleException $e) {
            return response()->json(['msg' => 'error connecting to real time server'],500);
        }
        return response()->json(['msg' => 'update successful']);
    }

    public function index ()
    {
        return Bus::all();
    }
}
