<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 06/08/2018
 * Time: 20:38
 */

namespace App\Http\Controllers;


use App\CompanyNotification;

class LineHelper
{
    private $line;

    /**
     * LineHelper constructor.
     * @param $line
     */
    public function __construct($line)
    {
        $this->line = $line;
    }


    public function getCurrentAlerts ()
    {
        $line = $this->line;
        $id = $line->id;
        $notificationsWithLines = CompanyNotification::whereHas('lines',function ($query) use ($line, $id) {
            $query->where('line_id','=',$id);
        })->whereRaw('(end_datetime > CURRENT_TIMESTAMP() or end_datetime IS NULL)')
            ->whereRaw('start_datetime < CURRENT_TIMESTAMP()')
            ->where('type','=','1')
            ->with('lines')
            ->get();
        $notificationsWithoutLines = CompanyNotification::where('transport_mode_id','=',$line->transport_mode_id)
            ->whereRaw('(end_datetime > CURRENT_TIMESTAMP()or end_datetime IS NULL)')
            ->where('type','=','1')
            ->doesntHave('lines')
            ->with('lines')
            ->get();
        $notificationsArray = $notificationsWithoutLines->toArray();
        $notificationsArray = array_merge($notificationsArray,$notificationsWithLines->toArray());
        return $notificationsArray;

    }

}