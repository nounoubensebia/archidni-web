<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/02/18
 * Time: 21:43
 */
class DynamicContext
{
    private $context;

    /**
     * DynamicContext constructor.
     * @param $context
     */
    public function __construct($context = [])
    {
        $this->context = $context;
    }

    public function setData($key,$data)
    {
        $this->context[$key] = $data;
    }

    public function getData($key)
    {
        return $this->context[$key];
    }

}