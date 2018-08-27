<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:47
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpPath
{
    private $directWalking;
    private $pathFinderAttributes;
    private $instructions;

    /**
     * OtpPath constructor.
     * @param $directWalking
     * @param $pathFinderAttributes
     * @param $instructions
     */
    public function __construct($directWalking, $pathFinderAttributes, $instructions)
    {
        $this->directWalking = $directWalking;
        $this->pathFinderAttributes = $pathFinderAttributes;
        $this->instructions = $instructions;
    }

    /**
     * @return mixed
     */
    public function getDirectWalking()
    {
        return $this->directWalking;
    }

    /**
     * @return mixed
     */
    public function getPathFinderAttributes()
    {
        return $this->pathFinderAttributes;
    }

    /**
     * @return mixed
     */
    public function getInstructions()
    {
        return $this->instructions;
    }



}