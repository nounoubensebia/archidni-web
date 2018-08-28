<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 15:43
 */

namespace App\Http\Controllers\OtpPathFinder;


class Coordinate implements \JsonSerializable
{
    private $latitude;
    private $longitude;

    /**
     * Coordinate constructor.
     * @param $latitude
     * @param $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = (double)$latitude;
        $this->longitude = (double)$longitude;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $var = get_object_vars($this);
        foreach ($var as &$value) {
            if (is_object($value) && method_exists($value,'getJsonData')) {
                $value = $value->getJsonData();
            }
        }
        return $var;
    }
}