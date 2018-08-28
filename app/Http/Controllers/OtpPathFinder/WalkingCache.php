<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 17:23
 */

namespace App\Http\Controllers\OtpPathFinder;


class WalkingCache
{
    private $walkingCacheEntries;
    /**
     * WalkingCache constructor.
     * @param $walkingCachEntries
     */
    public function __construct($walkingCacheEntries)
    {
        $this->walkingCacheEntries = $walkingCacheEntries;
    }

    public function contains (WalkingCacheEntry $walkingCacheEntry)
    {
        return in_array($walkingCacheEntry,$this->walkingCacheEntries,true);
    }

    public function getEntry (Coordinate $origin,Coordinate $destination)
    {
        foreach ($this->walkingCacheEntries as $walkingCacheEntry)
        {
            /**
             * @var $walkingCacheEntry WalkingCacheEntry
             */
            if ($walkingCacheEntry->getOrigin()==$origin&&$walkingCacheEntry->getDestination()==$destination)
            {
                return $walkingCacheEntry;
            }
        }
        return null;
    }

    public function addEntry(WalkingCacheEntry $walkingCacheEntry)
    {
        if (!$this->contains($walkingCacheEntry))
            array_push($this->walkingCacheEntries,$walkingCacheEntry);
    }

}