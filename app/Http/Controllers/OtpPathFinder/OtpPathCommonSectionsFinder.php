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
     * @var Context
     */
    private $context;

    /**
     * OtpPathCommonSectionsFinder constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param $path OtpPathIntermediate
     */
    public function addPossibleTrips ($path)
    {
        foreach ($path->getInstructions() as $instruction)
        {
            /**
             * @var $instruction Instruction
             */
            if ($instruction instanceof RideInstructionIntermediate)
            {
                $commonSectionFinder = new CommonSectionsFinder($this->context,$instruction,12,1);
                $commonSectionFinder->addPossibleTrips();
            }
        }
    }


}