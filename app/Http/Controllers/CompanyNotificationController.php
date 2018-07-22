<?php

namespace App\Http\Controllers;

use App\CompanyNotification;
use App\Line;
use App\TransportMode;
use Illuminate\Http\Request;

class CompanyNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $companyNotifications = CompanyNotification::with('lines','transportMode')->
        whereRaw('end_datetime > CURRENT_TIMESTAMP()')->orWhereRaw('end_datetime IS NULL');
        $mobileHeader = $request->header('mobile');
        if (isset($mobileHeader))
        {
            $companyNotifications = $companyNotifications->whereRaw('start_datetime < CURRENT_TIMESTAMP()');
        }
        $companyNotifications = $companyNotifications->get();
        return response()->json($companyNotifications);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $lines = Line::all();
        $transportModes = TransportMode::all();
        $informations = ['lines' => $lines, 'transportModes' => $transportModes];
        return response()->json($informations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $title = $request->input('title');
        $type = $request->input('type');
        $description = $request->input('description');
        $transportMode = $request->input('transport_mode_id');
        $lines = $request->input('lines');
        $startDatetime = $request->input('start_datetime');
        $endDatetime = $request->input('end_datetime');
        $companyNotification = new CompanyNotification(
            [   'title' => $title,
                'type' => $type,
                'transport_mode_id' =>$transportMode,
                'description' => $description,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime]
        );
        $lines = Line::find($lines);
        if ($companyNotification->save()) {
            $companyNotification->lines()->attach($lines);
            $resonse = [
                'msg' => 'notification created',
                'notification' => $companyNotification
            ];
            return response()->json($resonse, 201);
        } else {
            $resonse = [
                'msg' => 'an error has happened'
            ];
            return response()->json($resonse, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $notification = CompanyNotification::findOrFail($id)->with('lines','transportMode')->get();
        return response()->json($notification,200);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $notification = CompanyNotification::find($id)->with('lines')->get();
        $lines = Line::all();
        $transportModes = TransportMode::all();
        $informations = ['lines' => $lines, 'transportModes' => $transportModes];
        $neededFields = ['notification' => $notification,'informations' => $informations];
        return response()->json($neededFields,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $title = $request->input('title');
        $type = $request->input('type');
        $description = $request->input('description');
        $startDatetime = $request->input('start_datetime');
        $endDatetime = $request->input('end_datetime');

        $companyNotification = CompanyNotification::find($id);

        if (isset($title))
            $companyNotification->title = $title;
        if (isset($type))
            $companyNotification->type = $type;
        if (isset($description))
            $companyNotification->description = $description;
        if (isset($startDatetime))
            $companyNotification->start_datetime= $startDatetime;
        if (isset($endDatetime))
            $companyNotification->end_datetime= $endDatetime;

        if ($companyNotification->save()) {
            $resonse = [
                'msg' => 'notification updated',
                'notification' => CompanyNotification::find($id)->with('lines')->get()
            ];
            return response()->json($resonse, 201);
        } else {
            $resonse = [
                'msg' => 'an error has happened'
            ];
            return response()->json($resonse, 404);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $companyNotification = CompanyNotification::findOrFail($id);
        if ($companyNotification->delete())
        {
            return response()->json(
                ['msg' => 'notification deleted']
            ,200);
        }
        else
        {
            return response()->json(
                ['msg' => 'an error has happened']
                ,200);
        }
    }
}
