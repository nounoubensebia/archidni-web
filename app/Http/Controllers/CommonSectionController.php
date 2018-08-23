<?php
/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 23/08/18
 * Time: 14:03
 */
namespace App\Http\Controllers;

use App\CommonSection;
use App\MetroTrip;

class CommonSectionController
{
    private function findCommonSection($idStation1, $idStation2)
    {
        $commonSection = \App\CommonSection::where(function ($query) use ($idStation1, $idStation2) {
            $query->where('station1_id','=',$idStation1)
                ->where('station2_id','=',$idStation2);
        })->orWhere(function ($query) use ($idStation1, $idStation2){
            $query->where('station1_id','=',$idStation2)
                ->where('station2_id','=',$idStation1);
        })->get();
        if(count($commonSection) == 0) {
            $commonSection = new CommonSection();
            $commonSection->station1_id = $idStation1;
            $commonSection->station2_id = $idStation2;
            $commonSection->save();
        }
        else
            $commonSection = $commonSection[0];
        return $commonSection;
    }

    /**
     * @param $idTrip
     * @param $metroTrip
     * @return \App\MetroTrip if $metroTrip is true
     *         \App\TrainTrip else
     */
    private function findTrip($idTrip,$metroTrip)
    {
        if($metroTrip)
            $trip = \App\MetroTrip::find($idTrip);
        else
            $trip = \App\TrainTrip::find($idTrip);
        return $trip;
    }

    private function addTripToCommonSectionFromId($idTrip, $idStation1, $idStation2,$metroTrip)
    {
        $trip = $this->findTrip($idTrip,$metroTrip);
        $this->addTripToCommonSection($trip,$idStation1,$idStation2,$metroTrip);
    }

    private function addTripToCommonSection($trip, $idStation1, $idStation2,$metroTrip)
    {
        $commonSection = $this->findCommonSection($idStation1,$idStation2);
        $tripField = ($metroTrip)?"metro_trip_id":"train_trip_id";
        /**
         * @var $trip MetroTrip
         */
        $pivot = $trip->commonSections()->where($tripField, '=',$trip->id)
            ->where("common_section_id", '=', $commonSection->id)->get();
        if(count($pivot) == 0)
            $trip->commonSections()->newPivot([$tripField => $trip->id,
                "common_section_id" => $commonSection->id])
                ->save();
    }

    private function addCommonSections($trip1,$trip2,$metroTrip)
    {
        $stations1 = $trip1->stations;
        $stations2 = $trip2->stations;
        for($i=0;$i<count($stations1);$i++)
        {
            for($j=0;$j<count($stations2);$j++)
            {
                if($stations1[$i]->id == $stations2[$j]->id)
                {
                    $saveId = $stations1[$i]->id;
                    $lastId = $saveId;
                    $k = 1;
                    while ($i+$k < count($stations1) and
                        $j+$k < count($stations2) and
                        $stations1[$i+$k]->id == $stations2[$j+$k]->id) // while station is the same
                    {
                        $lastId = $stations1[$i+$k]->id;
                        $k++;
                    }

                    if($saveId != $lastId) // there is common section:
                    {
                        $i = $i + $k -1;
//                        $j = $j + $k -1;
                        $this->addTripToCommonSection($trip1,$saveId,$lastId,$metroTrip);
                        $this->addTripToCommonSection($trip2,$saveId,$lastId,$metroTrip);
                        break;
                    }
                }
            }
        }
    }
    private function createCommonSectionsFromTrips($trips,$metroTrip)
    {
        $start = 1;
        foreach ($trips as $trip)
        {
            for($i=$start;$i<count($trips);$i++)
                $this->addCommonSections($trip,$trips[$i],$metroTrip);
            $start++;
        }
    }

    public function fillDatabase()
    {
//        $this->addTripToCommonSection(1,7,2,true);
        $metroTrips = \App\MetroTrip::all();
        $this->createCommonSectionsFromTrips($metroTrips,true);
        $trainTrips = \App\TrainTrip::all();
        $this->createCommonSectionsFromTrips($trainTrips,false);
    }

}