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
        $instruction['duration']=$node['waitingTime'];
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
        //TODO FIX ME
        $instruction['duration'] = 25;
        $instruction['destination'] = "Test";
        $stations= array();
        foreach ($nodes as $node)
        {
            $station=[
                'name' => $node['name'],
                'coordinate' => ['latitude' => $node['latitude'],'longitude' => $node['longitude']]
            ];
            array_push($stations,$station);
        }
        $instruction['stations'] = $stations;
        return $instruction;
    }

    /*private function isTripGoingFromOriginToDestination ($line,$section)
    {
        $lineSections = $line->sections;
        foreach ($lineSections as $lineSection)
        {
            if ($lineSection->origin_id==$section->origin_id&&$lineSection->destination_id==$section->destination_id)
            {
                if ($lineSection->mode == 0||$lineSection->mode == 1)
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
                if ($lineSection->origin_id == $section['destination_id']&&$lineSection->destination_id&&$lineSection->)
                {

                }
            }
        }
    }*/

}