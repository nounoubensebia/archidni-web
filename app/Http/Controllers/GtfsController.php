<?php

namespace App\Http\Controllers;

use App\Http\Gtfs\GtfsCreator;
use Illuminate\Http\Request;

class GtfsController extends Controller
{
    //
    public function createFeed ()
    {
        $gtfsCreator = new GtfsCreator();
        $gtfsCreator->createGtfsFeed();
    }

    public function deployFeed ()
    {
        $gtfsCreator = new GtfsCreator();
        $gtfsCreator->deployGtfsFeed();
    }
}
