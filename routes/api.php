<?php

use App\Http\Resources\LineCollection;
use App\Http\Resources\LineResource;
use App\Line;
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
//Route::get('/test',function () {
//    //$lines = \App\Line::with(['operator','transportMode','trainTrips','metroTrips'])->with('sections')->get();
//
//});
//Route::group(['prefix' => 'v1'],function (){
//    Route::group(['prefix' => 'line'],function ()
//    {
//        Route::get('',[
//            'uses' => 'LineController@getLinesCloseToPosition'
//        ])->name('lines_close_to_position');
//        Route::get('autocomplete',[
//            'uses' => 'LineController@getLineAutocompleteSuggestions'
//        ])->name('line_autocomplete');
//        Route::get('{id}',[
//            'uses' => 'LineController@getLine'
//        ])->name('line');
//    });
//    Route::group(['prefix' => '/station'],function (){
//        Route::get('autocomplete',[
//           'uses' => 'StationController@getStationAutocompleteSuggestions'
//        ])->name('station_autocomplete');
//        Route::get('{id}',[
//            'uses' => 'StationController@getStation'
//        ]);
//        Route::get('{id}/lines',[
//            'uses' => 'LineController@getLinesPassingByStation'
//        ])->name('lines_passing_by_station');
//
//    });
//
//});

