<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 15:37
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\GeoUtils;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;
use App\Http\Controllers\PathFinderApi\Polyline;
use Hamcrest\Util;

class WalkingPathAdjuster
{
    /**
     * @var OtpPathIntermediate
     */
    private $path;
    /**
     * @var WalkingCache
     */
    private $walkingCache;

    private static $adjustementRate = 5;

    /**
     * WalkingPathAdjuster constructor.
     * @param OtpPathIntermediate $path
     * @param $walkingCache
     */
    public function __construct(OtpPathIntermediate $path, $walkingCache)
    {
        $this->path = $path;
        $this->walkingCache = $walkingCache;
    }

    public function getAdjustedPath ()
    {
        $newPath = [];
        foreach ($this->path->getInstructions() as $instruction)
        {
            if ($instruction instanceof WalkInstruction)
            {
                $streetPolyline = $this->walkingCache->getEntry($instruction->getOrigin(),$instruction->getDestination())->getPolyline();
                $directPolyline = Polyline::encode([
                    [$instruction->getOrigin()->getLatitude(),$instruction->getOrigin()->getLongitude()],
                    [$instruction->getDestination()->getLatitude(),$instruction->getDestination()->getLongitude()]
                ]);
                if ($this->takeStreetPolyline($streetPolyline,$directPolyline))
                {
                    $instruction->setPolyline($streetPolyline);
                    $instruction->setDuration(GeoUtils::getPolylineDuration($streetPolyline));
                }
                else
                {
                    $instruction->setPolyline($directPolyline);
                    $instruction->setDuration(GeoUtils::getPolylineDuration($directPolyline));
                }
                array_push($newPath,$instruction);
            }
            else
            {
                array_push($newPath,$instruction);
            }
        }
        return new OtpPathIntermediate($this->path->getDirectWalking(),$this->path->getPathFinderAttributes(),$newPath);
    }

    private function takeStreetPolyline ($streetPolyline,$directPolyline)
    {
        $dur1 = GeoUtils::getPolylineDuration($streetPolyline);
        $dur2 = GeoUtils::getPolylineDuration($directPolyline);
        $dur2+=0.0001;
        $diff = $dur1/$dur2;
        return $diff<self::$adjustementRate;
    }

}