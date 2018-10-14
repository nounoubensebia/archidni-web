<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OtpPathFinder\Coordinate;
use App\Http\Controllers\OtpPathFinder\Utils;
use App\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        $places = Place::with('hospital','parking')->get();
        return response()->json($places,200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

    }

    public function getNearbyPlaces ($id)
    {
        $place = Place::find($id);
        return Utils::getNearbyPlaces(new Coordinate($place->latitude,$place->longitude));
    }

}
