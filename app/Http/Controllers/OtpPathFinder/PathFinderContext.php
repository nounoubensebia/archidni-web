<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 30/08/2018
 * Time: 22:52
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\DataLoader\PathsDataLoader;

class PathFinderContext extends \App\Http\Controllers\Context
{
    /**
     * @var PathFinderAttributes
     */
    protected $pathFinderAttributes;
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



    /**
     * @return PathFinderAttributes
     */
    public function getPathFinderAttributes(): PathFinderAttributes
    {
        return $this->pathFinderAttributes;
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