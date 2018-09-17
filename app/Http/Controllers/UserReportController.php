<?php

namespace App\Http\Controllers;

use App\UserReportDisruption;
use App\UserReportOther;
use App\UserReportPath;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    //

    public function getDisruptionReports (Request $request)
    {
        return response()->json(UserReportDisruption::with('line','transportMode','user')->get(),200);
    }

    public function storeDisruptionReport (Request $request)
    {
        $description = $request->input('description');
        $user_id = $request->input('user_id');
        $line_id = $request->input('line_id');
        $transport_mode_id = $request->input('transport_mode_id');
        $report = new UserReportDisruption(['description'=>$description,'user_id'=>$user_id,'transport_mode_id'=>$transport_mode_id]);
        if (isset($line_id))
        {
            $report->line_id = $line_id;
        }
        if ($report->save())
        {
            return response()->json(['message'=>'report created !'],200);
        }
        else
        {
            return response()->json(['message'=>'error creating report !'],500);
        }
    }

    public function getOtherReports (Request $request)
    {
        return response()->json(UserReportOther::with('user')->get(),200);
    }

    public function storeOtherReport (Request $request)
    {
        $user_id = $request->input('user_id');
        $description = $request->input('description');

        $report = new UserReportOther(['description' => $description,'user_id' => $user_id]);

        if ($report->save())
        {
            return response()->json(['message'=>'report created !'],200);
        }
        else
        {
            return response()->json(['message'=>'error creating report !'],500);
        }
    }

    public function getPathReports (Request $request)
    {
        return response()->json(UserReportPath::with('user')->get(),200);
    }

    public function storePathReport (Request $request)
    {
        $user_id = $request->input('user_id');
        $description = $request->input('description');
        $path_data = $request->input('path_data');
        $is_good = $request->input('is_good');

        $report = new UserReportPath(['user_id'=>$user_id,'path_data'=>$path_data,'is_good'=>$is_good]);

        if (isset($description))
        {
            $report->description = $description;
        }

        if ($report->save())
        {
            return response()->json(['message'=>'report created !'],200);
        }
        else
        {
            return response()->json(['message'=>'error creating report !'],500);
        }
    }

}
