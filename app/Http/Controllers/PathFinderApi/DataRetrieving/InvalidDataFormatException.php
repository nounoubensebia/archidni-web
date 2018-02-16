<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 09/02/18
 * Time: 14:57
 */
class InvalidDataFormatException extends Exception
{
    public function errorMessage() {
        //error message
        $errorMsg = 'Format error : '.$this->getMessage();
        return $errorMsg;
    }
}