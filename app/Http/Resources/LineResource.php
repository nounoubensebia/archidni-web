<?php

namespace App\Http\Resources;

use App\Line;
use App\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\Resource;

class LineResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $sections = $this->sections;
        $sectionsCollection = collect();

        foreach ($sections as $section)
        {
            $id = $section->id;
            $origin = $section->origin;
            $destination = $section->destination;
            $order = $section->pivot->order;
            $mode = $section->pivot->mode;
            $sectionsCollection->push(['id' => $id,'origin' => $origin,'destination' => $destination,'order' => $order,
                'mode' => $mode]);
        }
        $sortedSections = $sectionsCollection->sortBy('order');

        if ($request->has('northeast'))
            return [
                'id' => $this->id,
                'name' => $this->name,
                'sections' => $sortedSections->values()->all(),
                'transport_mode_id' =>$this->transport_mode_id
            ];
        else
        {
            $trips = (($this->transport_mode_id==2) ? $this->trainTrips : $this->metroTrips);
            $tripsCollection = collect();
            foreach ($trips as $trip)
            {
                $tripArray = array();
                $tripArray['id'] = $trip->id;
                $tripArray['days'] = $trip->days;
                if ($this->transport_mode_id==2)
                {
                    $departures = array();
                    foreach ($trip->departures as $departure)
                    {
                        array_push($departures,['time'=>$departure->time]);
                    }
                    $tripArray['departures'] = $departures;
                }
                else
                {
                    $timePeriods = array();
                    foreach ($trip->timePeriods as $timePeriod)
                    {
                        array_push($timePeriods,['start'=>$timePeriod->start,'end'=>$timePeriod->end,
                            'waitingTime'=>$timePeriod->waiting_time]);
                    }
                    $tripArray['time_periods'] = $timePeriods;
                }
                $stations = array();
                foreach ($trip->stations as $station)
                {
                     array_push($stations,['id' => $station->id,'minutes' => $station->pivot->minutes]);
                }
                $tripArray['stations'] = $stations;
                $tripsCollection->push($tripArray);
            }

            return  [
                'id' => $this->id,
                'name' => $this->name,
                'transport_mode_id' =>$this->transport_mode_id,
                'sections' => $sortedSections->values()->all(),
                'trips' =>$tripsCollection->all()
            ];
        }
    }
}
