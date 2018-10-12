<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 11/10/2018
 * Time: 23:17
 */

namespace App\Http\Controllers\DataUpdater;




use App\Line;
use App\MetroTrip;
use App\Section;
use App\Station;
use App\TimePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusLinesUpdater
{
    public function updateBusLines ($json)
    {
        $linesJson = json_decode($json);
        $badLines = [];
        $goodLines = [];

        //getting good and bad lines

        $newGoodLines = [];
        $newProblematicLines = [];

        foreach ($linesJson as $lineJson)
        {
           $line = Line::query()->where('transport_mode_id','=','3')->where(
               'number','=',$lineJson->number)->get();

           if ($line->count()!=0)
           {
                $line = $line->first();
                $allerStations = $this->getAllerStations($line);
                $retourStations = $this->getRetourStations($line);
                $allerStationsJson = $lineJson->stops_aller;
                $retourStationsJson = $lineJson->stops_retour;
                if (count($allerStations)!=count($allerStationsJson)||count($retourStations)
                !=count($retourStationsJson))
                {
                    array_push($badLines,["database line" =>$line,"json line"=> $lineJson]);
                }
                else
                {
                    array_push($goodLines,["database line" =>$line,"json line"=> $lineJson]);
                    array_push($newGoodLines,[
                        "number" => $lineJson->number,
                        "old_line" => $line,
                        "stations_aller" => $this->getAotuaStationsWithCoordinates($allerStationsJson,
                            $allerStations,$line->sections()->wherePivot('mode','=','0')->get()),
                        "stations_retour" => $this->getAotuaStationsWithCoordinates($retourStationsJson,
                            $retourStations,$line->sections()->wherePivot('mode','=','1')->get())
                    ]);
                }

           }
        }


        //adding good lines if they don't already exist

        foreach ($newGoodLines as $newLine)
        {
            if(!Line::where('number','=',$newLine['number'])->where('operator_id','=','4')->get()->count() > 0)
            {
                $prevStation = null;
                $prevStationJson = null;
                $line = new Line(['number' => $newLine['number'],
                    'name'=> "ligne ".$newLine['number'],
                    "transport_mode_id"=>3,"operator_id" => 4]);
                $line->save();
                $this->addNewStations($line,$newLine['stations_aller'],true);
                $this->addNewStations($line,$newLine['stations_retour'],false);
                $this->createTripsForNewLines($line);
            }
        }
        

        //updating lines


        return $newGoodLines;

        //updating good lines

        /*foreach ($goodLines as $goodLine)
        {
            $allerStations = $this->getAllerStations($goodLine['database line']);
            $retourStations = $this->getRetourStations($goodLine['database line']);
            $jsonAllerStations = $goodLine['json line']->stops_aller;
            $jsonRetourStations = $goodLine['json line']->stops_retour;
            $this->updateStations($jsonAllerStations,$allerStations);
            $this->updateStations($jsonRetourStations,$retourStations);
        }*/

        //delete old sections and stations

        /*return ["bad lines" => $badLines,"good lines" => $goodLines,"bad count"=>
        count($badLines),"good count" =>count($goodLines)];*/

    }



    private function createTripsForNewLines ($newLine)
    {

        $metroTripAller = new MetroTrip(['days'=>127,'line_id'=>$newLine->id,'direction'=>0]);
        $metroTripRetour = new MetroTrip(['days'=>127,'line_id'=>$newLine->id,'direction'=>0]);
        $metroTripAller->save();
        $metroTripRetour->save();
        $this->addNewStationsToTrip($metroTripAller,$this->getAllerSections($newLine));
        $this->addNewStationsToTrip($metroTripRetour,$this->getRetourSections($newLine));
        $timePeriod = new TimePeriod(["start"=>"07:00:00","end"=>"23:00:00","waiting_time"=>"7","metro_trip_id"=>
        $metroTripAller->id]);
        $timePeriod->save();
        $timePeriod = new TimePeriod(["start"=>"07:00:00","end"=>"23:00:00","waiting_time"=>"7","metro_trip_id"=>
            $metroTripRetour->id]);
        $timePeriod->save();
    }

    private function getAllerSections ($line)
    {
        return $line->sections()->wherePivot('mode','=','0')->get();
    }

    private function getRetourSections ($line)
    {
        return $line->sections()->wherePivot('mode','=','1')->get();
    }

    private function addNewStationsToTrip ($trip,$sections)
    {
        $duration = 0;
        foreach ($sections as $section)
        {
            $trip->stations()->attach($section->origin,['minutes'=>$duration]);
            $duration+=$section->durationPolyline;
        }
    }

    private function addNewStations ($line,$jsonStations,$aller)
    {
        $prevStation = null;
        $prevStationJson = null;
        $i=0;
        foreach ($jsonStations as $stationJson)
        {
            if (!Station::where('aotua_id','=',$stationJson['aotua_id'])->get()->count()>0)
            {
                $station = new Station(['aotua_id' => $stationJson['aotua_id'],'transport_mode_id'=>3,
                    'name' => $stationJson['name'],'latitude' => $stationJson['latitude'],
                    'longitude' => $stationJson['longitude']
                ]);
                $station->save();
            }
            else
            {
                $station = Station::where('aotua_id','=',$stationJson['aotua_id'])->get()->first();
            }

            if (isset($prevStation))
            {
                $section = new Section(['origin_id'=>$prevStation->id,'destination_id'=>$station->id,
                    'polyline'=>$prevStationJson['polyline'],
                    'durationPolyline'=>$prevStationJson['durationPolyline']]);
                $section->save();
                $mode = ($aller) ? 0:1;
                $line->sections()->attach($section,["order"=>$i,"mode"=>$mode]);
            }
            $prevStation = $station;
            $prevStationJson = $stationJson;
            $i++;
        }
    }



    private function getAotuaStationsWithCoordinates ($jsonStations,$databaseStations,$sections)
    {
        $stations = [];
        for($i=0;$i<count($jsonStations);$i++)
        {
            $jsonStation = $jsonStations[$i];
            $databaseStation = $databaseStations[$i];
            $station['aotua_id'] = $jsonStation->id;
            $station['name'] = $jsonStation->name;
            $station['latitude'] = $databaseStation->latitude;
            $station['longitude'] = $databaseStation->longitude;
            if ($i<$sections->count())
            {
                $station['polyline'] = $sections->get($i)->polyline;
                $station['durationPolyline'] = $sections->get($i)->durationPolyline;
            }
            else
            {
                $station['polyline'] = null;
                $station['durationPolyline'] = null;
            }
            array_push($stations,$station);
        }
        return $stations;
    }

    private function updateStations ($jsonStations,$databaseStations)
    {
        for ($i=0;$i<count($jsonStations);$i++)
        {
            $jsonStation = $jsonStations[$i];
            $station = $databaseStations[$i];
            if (!isset($station->aotua_id))
            {
                $station->aotua_id = $jsonStation->id;
                $station->name = $jsonStation->name;
                $station->save();
            }
        }
    }

    private function getAllerStations (Line $line)
    {
        $sections = $line->sections()->wherePivot('mode','=','0')->get();
        $stations = [];
        array_push($stations,$sections->first()->origin);
        foreach ($sections as $section)
        {
            array_push($stations,$section->destination);
        }
        return $stations;
    }

    private function getRetourStations (Line $line)
    {
        $sections = $line->sections()->wherePivot('mode','=','1')->get();
        $stations = [];
        array_push($stations,$sections->first()->origin);
        foreach ($sections as $section)
        {
            array_push($stations,$section->destination);
        }
        return $stations;
    }
}