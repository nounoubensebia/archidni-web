<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 31/07/2018
 * Time: 22:57
 */

namespace App\Http\Controllers\PathFinderApi;


class PathCombiner
{


    /**
     * PathCombiner constructor.
     * @param $path1
     * @param $path2
     */



    public function getCombinedPath ($path1,$path2)
    {
        $combinedPath = array();
        if (count($path1)!=count($path2))
        {
            return null;
        }
        else
        {
            for ($i=0;$i<count($path1);$i++)
            {
                $instruction1 = $path1[$i];
                $instruction2 = $path2[$i];
                if (strcmp($instruction1['type'],$instruction2['type'])==0)
                {
                    if (strcmp($instruction1['type'],"ride_instruction")==0)
                    {
                        $instruction = $this->getCombinedRideInstruction($instruction1,$instruction2);
                        if (isset($instruction))
                        {
                            array_pop($combinedPath);
                            array_push($combinedPath,$this->getCombinedWaitInstruction($path1[$i-1],$path2[$i-1]));
                            array_push($combinedPath,$instruction);
                        }
                        else
                        {
                            return null;
                        }
                    }
                    else
                    {
                        array_push($combinedPath,$instruction1);
                    }
                }
                else
                {
                    return null;
                }
            }
        }
        return $combinedPath;
    }

    public function getCombinedPaths ($paths)
    {
        $combinedPaths = array();
        $i=0;
        while ($i<count($paths))
        {
            $path1 = $paths[$i];
            $j=$i+1;
            while ($j<count($paths))
            {
                $path2 = $paths[$j];
                $cPath = $this->getCombinedPath($path1,$path2);
                if (isset($cPath))
                {
                    $path1 = $cPath;
                    unset($paths[$j]);
                    $paths = array_values($paths);
                }
                else
                {
                    $j++;
                }
            }
            array_push($combinedPaths,$path1);
            $i++;
        }
        return $combinedPaths;
    }

    private function getCombinedWaitInstruction ($instruction1,$instruction2)
    {
        $instruction = $instruction1;
        foreach ($instruction2['lines'] as $line)
        {
            array_push($instruction['lines'],$line);
        }
        $instruction['lines'] = array_unique($instruction['lines'],SORT_REGULAR);
        $instruction['lines'] = array_values($instruction['lines']);
        return $instruction;
    }

    private function getCombinedRideInstruction ($instruction1,$instruction2)
    {
        $instruction = array();
        $instruction['type']="ride_instruction";
        $instruction['transport_mode_id'] = $instruction1['transport_mode_id'];
        $stations = array();
        if (count($instruction1['stations'])!=count($instruction2['stations']))
        {
            return null;
        }
        for ($i=0;$i<count($instruction1['stations']);$i++)
        {
            $station1 = $instruction1['stations'][$i];
            $station2 = $instruction2['stations'][$i];
            if ($station1['id']!=$station2['id'])
            {
                return null;
            }
            array_push($stations,$station1);
        }

        $instruction['lines'] = array();
        foreach ($instruction1['lines'] as $line)
        {
            array_push($instruction['lines'],$line);
        }

        foreach ($instruction2['lines'] as $line)
        {
            array_push($instruction['lines'],$line);
        }

        $instruction['lines'] = array_unique($instruction['lines'],SORT_REGULAR);
        $instruction['lines'] = array_values($instruction['lines']);
        $instruction['stations'] = $stations;
        $instruction['duration'] = $instruction1['duration'];
        $instruction['polyline'] = $instruction1['polyline'];
        if (isset($instruction1['error_margin']))
        {
            $instruction['error_margin'] = $instruction1['error_margin'];
        }
        return $instruction;
    }
}