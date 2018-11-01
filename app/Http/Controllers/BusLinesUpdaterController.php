<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BusLinesUpdater\GeolocToTempConverter;
use Illuminate\Http\Request;

class BusLinesUpdaterController extends Controller
{
    //
    public function convertGeolocToTemp ()
    {
        $converter = new GeolocToTempConverter();
        return $converter->convert();
    }
}
