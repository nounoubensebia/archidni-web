<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 17:49
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


abstract class Instruction implements \JsonSerializable
{
    private $type;

    /**
     * Instruction constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }



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