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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//Route::get('/test',function () {
//    $lines = \App\Line::with(['operator','transportMode','trainTrips','metroTrips'])->with('sections')->get();
//    return response()->json($lines,200);
//});

/**
 * sample execution
 * index.php/api/findPath?origin=36.733245,3.156908&destination=36.769238,3.236513&time=5:30&day=2
 * */


Route::get("/create-station-region-map","PathServerController@createRegionStationMap");

Route::get('/testotp','test@testOTP');

Route::get('/test','test@test');

Route::get('/findPath', 'PathFinderController@findPath');

Route::get('/generatePath', 'PathFinderController@generatePath');

Route::get('/testOTP', 'PathFinderController@testOTP');

Route::post('/formatPaths','PathFinderController@formatPaths');

Route::get('/transferTest', ['uses' => "StationController@getTransfersTest"]);

Route::get('/create-gtfs',['uses' => "GtfsController@createFeed"]);

Route::get('/deploy-gtfs',["uses" => "GtfsController@deployFeed"]);

Route::get('/find-common-sections',['uses' => 'CommonSectionController@findCommonSections']);

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
        Route::get('{id}/schedules',[
            'uses' => 'LineController@getSchedules'
        ]);
        Route::get('{id}', [
            'uses' => 'LineController@getLine'
        ])->name('line');
    });

    Route::get('lines',[
        'uses' => 'LineController@index'
    ])->name('line');


    Route::group(['prefix' => '/station','middleware' => ['token.handler:api']], function () {
        Route::get('autocomplete', [
            'uses' => 'StationController@getStationAutocompleteSuggestions'
        ])->name('station_autocomplete');
        Route::get('{id}', [
            'uses' => 'StationController@getStation'
        ]);
        Route::get('{id}/lines', [
            'uses' => 'StationController@getLinesPassingByStation'
        ])->name('lines_passing_by_station');
        Route::get('{id}/transfers',[
            'uses' => 'StationController@getTransfers'
        ])->name('station_transfers');
        Route::get('{id}/nearby-places',[
           'uses' => 'StationController@getNearbyPlaces'
        ]);
    });


    Route::group(['prefix' => 'user'], function () {

        Route::post('signup', [
            'uses' => 'UserController@signup'
        ]);
        Route::post('login', [
            'uses' => 'UserController@login'
        ]);
        Route::put('{id}/update-password',[
           'uses' => 'UserController@updatePassword'
        ])->middleware('token.handler:api');
        Route::put('{id}/update',
            ['uses' => 'UserController@updateInfo'])->middleware('token.handler:api');
    });

    Route::group(['prefix' => 'user-reports'],function ()
    {
       Route::get('disruption',[
          'uses' => 'UserReportController@getDisruptionReports'
       ]);
       Route::post('disruption/create',
           ['uses' => 'UserReportController@storeDisruptionReport']);
       Route::get('path',[
           'uses' => 'UserReportController@getPathReports'
       ]);
       Route::post('path/create',
           ['uses' => 'UserReportController@storePathReport']
       );
       Route::get('other',[
           'uses' => 'UserReportController@getOtherReports'
       ]);
       Route::post('other/create',[
          'uses' => 'UserReportController@storeOtherReport'
       ]);
    });

    Route::post('/create-train-trips',
        ['uses' => 'TripController@createTrainTrips']);

    Route::get('/CompanyNotifications/admin',
        ['uses' => 'CompanyNotificationController@indexAdmin']);

    Route::post('/CompanyNotifications/add',
        ['uses' =>'CompanyNotificationController@store']);

    Route::resource('CompanyNotifications', 'CompanyNotificationController');



    Route::group(['prefix' => 'places'] ,function ()
    {
        Route::resource('', 'PlaceController');
        Route::resource('/parkings','ParkingController');
        Route::resource('/hospitals','HospitalController');
        Route::get('{id}/nearby-places','PlaceController@getNearbyPlaces');
    });

    Route::get('/buses/update',
        ['uses' => 'BusController@updateLocations']
    );

    Route::get('buses',[
        'uses' => 'BusController@index'
    ]);

    Route::group(['prefix' => 'update'],
        function ()
        {
            Route::post('/bus-lines',[
                'uses' => 'LineController@updateBusLines'
            ]);
        });
    Route::group(['prefix' => 'temp-bus'],
        function ()
        {
            Route::get('/lines',
                ['uses'=>'TempBusController@getLines']);
            Route::post('/store-lines',
                ['uses' => 'TempBusController@storeLinesFromWissJson']);
        }
        );
    Route::group(['prefix' => 'admin'],
        function ()
        {
           Route::post('/login',
               ['uses' => "AdminController@login"]);
        });
    Route::group(['prefix' => 'update-bus-lines'], function ()
        {
            Route::get('/clean-database',['uses' => 'BusLinesUpdaterController@cleanDatabase']);
            Route::get('/convert-geoloc-temp',['uses' => 'BusLinesUpdaterController@convertGeolocToTemp']);
            Route::get('/convert-production-temp',['uses' => 'BusLinesUpdaterController@convertProductionToTemp']);
            Route::get('/convert-temp-production',['uses' => 'BusLinesUpdaterController@convertTempToProduction']);
            Route::get('/diag',['uses' => 'BusLinesUpdaterController@diag']);
            Route::get('/generate-excel-files',['uses' => 'BusLinesUpdaterController@generateExcel']);
        }
    );
});

