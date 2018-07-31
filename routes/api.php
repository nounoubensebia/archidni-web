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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::get('/test',function () {
//    $lines = \App\Line::with(['operator','transportMode','trainTrips','metroTrips'])->with('sections')->get();
//    return response()->json($lines,200);
//});

/**
 * sample execution
 * index.php/api/findPath?origin=36.733245,3.156908&destination=36.769238,3.236513&time=5:30&day=2
 * */

Route::get('/test','test@test');

Route::get('/findPath', 'PathFinderController@findPath');

Route::get('/generatePath', 'PathFinderController@generatePath');


Route::get('/transferTest', ['uses' => "StationController@getTransfersTest"]);


Route::group(['prefix' => 'v1'], function () {
    Route::get('/linesAndPlaces', ['uses' => 'LinesAndPlacesController@getAllPlacesAndLines'])
        ->name('all_lines_and_places')
        ->middleware('token.handler:api');


    Route::group(['prefix' => 'line','middleware' => ['token.handler:api']], function () {
        Route::get('etusa', [
            'uses' => 'LineController@getEtusaLines'
        ])->name('etusa_lines');
        Route::get('/{id}/notifications', [
            'uses' => 'LineController@getNotifications'
        ]);
        Route::get('autocomplete', [
            'uses' => 'LineController@getLineAutocompleteSuggestions'
        ])->name('line_autocomplete');
        Route::get('{id}', [
            'uses' => 'LineController@getLine'
        ])->name('line');
    });


    Route::group(['prefix' => '/station','middleware' => ['token.handler:api']], function () {
        Route::get('autocomplete', [
            'uses' => 'StationController@getStationAutocompleteSuggestions'
        ])->name('station_autocomplete');
        Route::get('{id}', [
            'uses' => 'StationController@getStation'
        ]);
        Route::get('{id}/lines', [
            'uses' => 'LineController@getLinesPassingByStation'
        ])->name('lines_passing_by_station');

    });


    Route::group(['prefix' => 'user'], function () {

        Route::post('signup', [
            'uses' => 'UserController@signup'
        ]);
        Route::post('login', [
            'uses' => 'UserController@login'
        ]);
        Route::post('{id}/update-password',[
           'uses' => 'UserController@updatePassword'
        ])->middleware('token.handler:api');
    });

    Route::resource('CompanyNotifications', 'CompanyNotificationController')->middleware('token.handler:api');

});

