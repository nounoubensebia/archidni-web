<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
//Route::get('/test',function () {
//    $lines = \App\Line::with(['operator','transportMode','trainTrips','metroTrips'])->with('sections')->get();
//    return response()->json($lines,200);
//});

Route::get('/test','PathFinderController@findPath');

