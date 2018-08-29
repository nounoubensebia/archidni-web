<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 29/08/2018
 * Time: 17:02
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\PathInstruction\Instruction;
use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\PathFinderApi\CommonSectionsFinder;

class OtpPathCommonSectionsFinder
{
    /**
     * @var OtpPathIntermediate
     */
    private $path;

    /**
     * OtpPathCommonSectionsFinder constructor.
     * @param OtpPathIntermediate $path
     */
    public function __construct(OtpPathIntermediate $path)
    {
        $this->path = $path;
    }

    public function addPossibleTrips ()
    {
        foreach ($this->path->getInstructions() as $instruction)
        {
            /**
             * @var $instruction Instruction
             */
            if ($instruction instanceof RideInstructionIntermediate)
            {
                $commonSectionFinder = new CommonSectionsFinder($instruction,12,1);
                $commonSectionFinder->addPossibleTrips();
            }
        }
    }


}