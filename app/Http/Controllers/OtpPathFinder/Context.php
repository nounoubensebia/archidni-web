<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 30/08/2018
 * Time: 22:52
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\DataLoader\PathsDataLoader;

class Context
{
    /**
     * @var PathFinderAttributes
     */
    protected $pathFinderAttributes;
    protected $debug;
    protected $data;



    /**
     * Context constructor.
     * @param $debug
     */
    public function __construct($pathFinderAttributes)
    {
        $this->debug = array();
        $this->pathFinderAttributes = $pathFinderAttributes;
    }

    public function addToDebug ($key,$value)
    {
        $this->debug["$key"] = $value;
    }

    public function incrementValue ($key,$value)
    {
        if (isset($this->debug[$key]))
            $this->debug[$key] += $value;
        else
            $this->debug[$key] = $value;
    }

    public function pushValue ($key,$value)
    {
        if (isset($this->debug[$key]))
        {
            array_push($this->debug[$key],$value);
        }
        else
        {
            $this->debug[$key] = [];
            array_push($this->debug[$key],$value);
        }
    }

    /**
     * @return PathFinderAttributes
     */
    public function getPathFinderAttributes(): PathFinderAttributes
    {
        return $this->pathFinderAttributes;
    }



    /**
     * @return array
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }



    public function loadData ($paths)
    {
        $before = Utils::getTimeInMilis();
        $pathsDataLoader = new PathsDataLoader($this);
        $this->data = $pathsDataLoader->loadData($paths);
        $after = Utils::getTimeInMilis();
        $this->incrementValue("trip_load",($after-$before));
    }


}