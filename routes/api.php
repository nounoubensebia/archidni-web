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
Route::get('/test',function () {
    //$lines = \App\Line::with(['operator','transportMode','trainTrips','metroTrips'])->with('sections')->get();

});
Route::group(['prefix' => 'v1'],function (){
    Route::group(['prefix' => 'lines'],function ()
    {
        Route::get('',[
            'uses' => 'LineController@getLinesCloseToPosition'
        ]);

    });
    Route::group(['prefix' => '/station'],function (){
        Route::get('{id}/lines',[
            'uses' => 'LineController@getLinesPassingByStation'
        ]);
    });
});

