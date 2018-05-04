<?php

/**
 * Created by PhpStorm.
 * User: noure
 * Date: 22/02/2018
 * Time: 22:20
 */
class PathTransformer
{
    private $path;

    /**
     * PathTransformer constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getTransformedPath()
    {
        $instructions = array();
        $flag = false;
        array_push($instructions,$this->getWalkInstruction($this->path[0],$this->path[1]));
        if (isset($this->path[1]['idLine']))
        {
            array_push($instructions,$this->getWaitInstruction($this->path[1]));
            $i = 1;
            $reachedDestination = false;
            while (!$reachedDestination)
            {
                $rideNodes = array();
                while (strcmp($this->path[$i]['transportModeToNextNode'],"byFoot")!=0)
                {
                    array_push($rideNodes,$this->path[$i]);
                    $i++;
                }
                array_push($rideNodes,$this->path[$i]);
                if (count($rideNodes)>1)
                {
                        array_push($instructions,$this->getRideInstruction($rideNodes));
                        array_push($instructions,$this->getWalkInstruction($this->path[$i],$this->path[$i+1]));

                    if (isset($this->path[$i+1]['waitingTime']))
                    {
                        array_push($instructions,$this->getWaitInstruction($this->path[$i+1]));
                    }
                    else
                    {
                        $reachedDestination = true;
                    }
                }
                else
                {
                    $flag = true;
                    //array_pop($instructions);
                    //$waitInstruction = array_pop($instructions);
                    array_pop($instructions);
                    array_pop($instructions);
                    array_push($instructions,$this->getWalkInstruction($this->path[$i-1],$this->path[$i+1]));
                    array_push($instructions,$this->getWaitInstruction($this->path[$i+1]));
                    //$this->path[$i+1] = $this->path[$i+2];
                    //print_r($this->path[$i+1]);
                }
                $i++;
            }
        }
        return $instructions;
    }

    private function getWalkInstruction($oNode, $dNode)
    {
        $instruction = array();
        $instruction['type'] = 'walk_instruction';
        $polyline = [
            [
                'latitude' => $oNode['latitude'],
                'longitude' => $oNode['longitude']
            ],
            [
                'latitude' => $dNode['latitude'],
                'longitude' => $dNode['longitude']
            ]
        ];
        $instruction['polyline'] = $polyline;
        $instruction['destination_type'] = (isset($dNode['idLine']))? "station":"user_destination";
        $instruction['destination'] = $dNode['name'];
        return $instruction;
    }

    private function getWaitInstruction ($node)
    {
        $instruction = array();
        $instruction['type']='wait_instruction';
        $instruction['duration']=intval($node['waitingTime']);
        $instruction['exact_waiting_time'] = $node['exactWaitingTime'];
        $instruction['coordinate'] = [
            'latitude' => $node['latitude'],
            'longitude' => $node['longitude']
        ];
        return $instruction;
    }

    private function getRideInstruction ($nodes)
    {
        $instruction = array();
        $instruction['type']='ride_instruction';
        $line = \App\Line::find($nodes[0]['idLine']);
        $instruction['line_name'] = $line->name;
        $instruction['transport_mode_id'] = $line->transport_mode_id;
        $stations= array();
        foreach ($nodes as $node)
        {
            $station=[
                'name' => $node['name'],
                'coordinate' => ['latitude' => $node['latitude'],'longitude' => $node['longitude']],
                'id' => $node['idStation']
            ];
            array_push($stations,$station);
        }
        $instruction['duration'] = $this->getRideDuration($nodes);
        $instruction['destination'] = $this->getTripDestination($line,['origin_id'=>$stations[0]['id'],
            'destination_id'=>$stations[1]['id']]);
        $instruction['stations'] = $stations;
        return $instruction;
    }

    private function getRideDuration ($nodes)
    {
        $duration = 0;
        $lastNode = null;
        foreach ($nodes as $node)
        {
            $duration+=$node['timeToNextNode'];
            $lastNode = $node;
        }
        $duration -= intval($lastNode['timeToNextNode']);
        return intVal($duration);
    }

    private function isTripGoingFromOriginToDestination ($line,$section)
    {
        $lineSections = $line->sections;
        foreach ($lineSections as $lineSection)
        {
            if ($lineSection->origin_id==$section['origin_id']&&$lineSection->destination_id==$section['destination_id'])
            {
                if ($lineSection->mode == 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                if ($lineSection->origin_id == $section['destination_id']
                    &&$lineSection->destination_id==$section['origin_id'])
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function getLineFirstStop ($line)
    {
        $sections = \App\Section::query()->whereHas('lines',function ($q) use ($line)
        {
            $q->where('line_id','=',$line->id)->orderBy('order')->orderBy('mode');
        })
            ->get()->all();
        return $sections[0]->origin->name;
    }

    private function getLineLastStop ($line)
    {
        $sections = \App\Section::query()->whereHas('lines',function ($q) use ($line)
        {
            $q->where('line_id','=',$line->id)->orderByDesc('order')->orderBy('mode');
        })
            ->get()->all();
        return $sections[count($sections)-1]->destination->name;
    }

    private function getTripDestination ($line,$section)
    {
        if ($this->isTripGoingFromOriginToDestination($line,$section))
        {
            return $this->getLineLastStop($line);
        }
        else
        {
            return $this->getLineFirstStop($line);
        }
    }

}