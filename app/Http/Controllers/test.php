<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Controllers\OtpPathFinder\Coordinate;
use App\Http\Controllers\OtpPathFinder\WalkingCacheEntry;
use App\Http\Controllers\PathFinderApi\OtpPathFormatter;
use App\MetroTrip;
use Illuminate\Http\Request;
use UtilFunctions;


class test extends Controller
{
    //
    public function test (Request $request)
    {
        $trip = MetroTrip::with('stations')->find(202)->first();
        return response()->json($trip->stations);
    }

    public function testOTP (Request $request)
    {
        $json = file_get_contents("http://localhost:8801/otp/routers/default/plan?fromPlace=36.7313184,3.1729445&toPlace=36.7623459,2.9225893&time=10:02am&date=11-14-2018&mode=TRANSIT,WALK&arriveBy=false&numItineraries=10");
        $resp = new OtpPathFormatter($json);
        return response()->json($resp->getFormattedPaths());
    }
}
