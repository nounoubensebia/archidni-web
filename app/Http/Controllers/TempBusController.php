<?php

namespace App\Http\Controllers;

use App\Http\Controllers\DataUpdater\BusLinesUpdater;
use App\Station;
use App\TempBusLine;
use App\TempBusStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TempBusController extends Controller
{
    //
    public function getLines ()
    {
        /*$lines = TempBusLine::with('tempBusStations','tempBusStations.locations')->get()->toArray();
        foreach ($lines as &$line)
        {
            $line['state'] = $this->getLineState($line);
        }
        return $lines;*/
        $busUpdater = new BusLinesUpdater();
        $resp = $busUpdater->archidniAllerToGeoLocAller();
        return $resp;
    }


    public function storeLinesFromWissJson(Request $request)
    {
        DB::disableQueryLog();
        DB::connection()->disableQueryLog();
        $json = $request->getContent();
        $lines = json_decode($json);
        $stations = $this->getStationsFromLines($lines);
        //$stations = array_slice($stations, 100, 200);;
        $this->storeStations($stations);
        foreach ($lines as $line)
        {
            $number = $line->number;
            $linea = new TempBusLine(["number" => $number]);
            $linea->save();
            foreach ($line->stops_aller as $stop)
            {
                $station = TempBusStation::where("aotua_id",$stop->id)->first();
                $linea->tempBusStations()->attach($station,['order' => $stop->order]);
            }
            foreach ($line->stops_retour as $stop)
            {
                $station = TempBusStation::where("aotua_id",$stop->id)->first();
                $linea->tempBusStations()->attach($station,['order' => -1*$stop->order]);
            }
        }
    }

    private function getStationsFromLines ($lines)
    {
        $stations = [];
        foreach ($lines as $line)
        {
            foreach ($line->stops_aller as $stop)
            {
                array_push($stations,['aotua_id'=>$stop->id,"name"=>$stop->name]);
            }
            foreach ($line->stops_retour as $stop)
            {
                array_push($stations,['aotua_id'=>$stop->id,"name"=>$stop->name]);
            }
        }
        return array_values(array_unique($stations,SORT_REGULAR));
    }

    private function storeStations ($stations)
    {

        $stationToSave = [];
        foreach ($stations as $station)
        {
            $temp_stop = TempBusStation::where("aotua_id",$station['aotua_id'])->first();
            if ($temp_stop==null)
            {
                /*$temp_stop = new TempBusStation(['aotua_id'=>$station['aotua_id'],
                    'name'=>$station['name']]);*/
                array_push($stationToSave,['aotua_id'=>$station['aotua_id'],
                    'name'=>$station['name']]);
            }
        }
        foreach ($stationToSave as $item)
        {
            DB::table("temp_bus_stations")->insert($item);
        }
    }

    function arrayToCsv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ( $fields as $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }

        return implode( $delimiter, $output );
    }


    private function getLineState ($line)
    {
        $stations = $line['temp_bus_stations'];
        $verifiedA = 0;
        $verifiedR = 0;
        $locatedA = 0;
        $locatedR = 0;
        $totalA = 0;
        $totalR = 0;
        foreach ($stations as $station)
        {
            $locations = $station['locations'];
            if ($station['pivot']['order']>0)
            {
                $totalA++;
                if (count($locations)>0)
                {
                    $locatedA++;
                }
            }
            else
            {
                $totalR++;
                if (count($locations)>0)
                {
                    $locatedR++;
                }
            }
            foreach ($locations as $location)
            {
                if ($location['is_verified']==1)
                {
                    if ($station['pivot']['order']>0)
                    {
                        $verifiedA++;
                        continue;
                    }
                    else
                    {
                        $verifiedR++;
                        continue;
                    }
                }
            }
        }

        return ["total_a" => $totalA,"verified_a" => $verifiedA,"located_a"=>$locatedA,
            "total_r" => $totalR,"verified_r" => $verifiedR,"located_r"=>$locatedR];
    }
}
