<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 23/08/2018
 * Time: 21:57
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpPathHelper
{
    private $path;

    /**
     * OtpPathHelper constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getWalkingInstructions ()
    {
        foreach ($this->path as $instruction)
        {
            if (strcmp($instruction['type'],"walk_instruction"))
            {

            }
        }
    }

}