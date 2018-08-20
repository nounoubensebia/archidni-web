<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PathFinderApi\PathServerHelper;
use Illuminate\Http\Request;

class PathServerController extends Controller
{
    //
    public function createRegionStationMap ()
    {
        $pathServerHelper = new PathServerHelper();
        return response()->json($pathServerHelper->createStationRegionsMap());
    }
}
