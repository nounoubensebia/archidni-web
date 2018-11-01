<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/11/2018
 * Time: 22:02
 */

namespace App\Http\Controllers\BusLinesUpdater;


use App\Http\Controllers\PathFinderApi\Polyline;
use App\Line;
use App\Section;
use App\Station;
use App\TempBusLine;

class TempToProductionConverter
{
    public function convert()
    {
        echo "here";
        $converter = new GeolocToTempConverter();
        $tempLines = $converter->getCompleteLines()['complete lines'];
        print_r($tempLines);
        //$this->storeStations($tempLines);
        $this->storeLines($tempLines);
        $this->storeSections($tempLines);
    }


    private function storeLines ($tempLines)
    {
        foreach ($tempLines as $tempLine)
        {
            $line = new Line(['name' => "linge ".$tempLine->number,'transport_mode_id' => 3,'number' => $tempLine->number,
                'operator_id' =>4]);
            $line->save();
        }
    }

    private function storeSections ($tempLines)
    {
        foreach ($tempLines as $tempLine)
        {
            $prodLine = $this->getProdLineByNumber($tempLine->number);
            $allerStations = BusLinesUpdaterUtils::getAllerStations($tempLine);
            $retourStations = BusLinesUpdaterUtils::getRetourStations($tempLine);
            $this->buildSections($prodLine,$this->getProductionStationsFromTemp($allerStations),0);
            $this->buildSections($prodLine,$this->getProductionStationsFromTemp($retourStations),1);
        }
    }

    private function getProdLineByNumber ($number)
    {
        $lines = Line::all();
        foreach ($lines as $line)
        {
            if ($line->operator_id == 4 && $line->number == $number)
            {
                return $line;
            }
        }
        return null;
    }

    private function getProductionStationsFromTemp ($tempStations)
    {
        $stations = [];
        foreach ($tempStations as $tempStation)
        {
            $station = Station::where("aotua_id","=",$tempStation->aotua_id)->get()->first();
            array_push($stations,$station);
        }
        return $stations;
    }

    private function buildSections ($productionLine,$stations,$type)
    {
        $origin = $stations[0];
        $i=0;
        foreach ($stations as $station)
        {
            $section = new Section(['origin_id'=>$origin->id,'destination_id'=>$station->id,
                'polyline'=>Polyline::encode([[$origin->latitude,$origin->longitude],[
                    $station->latitude,$station->longitude
                ]]),
                'durationPolyline'=>1]);
            $section->save();
            $productionLine->sections()->attach($section,["order"=>$i,"mode"=>$type]);
            $origin = $station;
            $i++;
        }
    }

    private function storeStations ($tempLines)
    {
        foreach ($tempLines as $tempLine)
        {
            $tempStations = $tempLine->stations;
            foreach ($tempStations as $tempStation)
            {
                if (Station::where('aotua_id','=',$tempStation->aotua_id)->get()->first()==null)
                {
                    $location = BusLinesUpdaterUtils::getTempStationLocation($tempStation);
                    $station = new Station(['aotua_id'=>$tempStation->aotua_id,
                        'name' => $tempStation->name,
                        'transport_mode_id' => 3,
                        'latitude' =>$location->latitude,
                        'longitude' => $location->longitude]);
                    $station->save();
                }
            }
        }
    }
}