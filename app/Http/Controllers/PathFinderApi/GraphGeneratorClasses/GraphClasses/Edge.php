<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 19/08/17
 * Time: 17:41
 */
class Edge
{
    private $node1;
    private $node2;
    //TODO implement varients of weight
    private $weight;
    private $data;
    /**
     * Edge constructor.
     * @param $node1
     * @param $node2
     * @param $value
     */

    public function __construct($node1, $node2, $value = 0.0)
    {
        $this->node1 = $node1;
        $this->node2 = $node2;
        $this->weight = $value;
        $this->data = array();
    }


    /**
     * @return Node
     */
    public function getNode1()
    {
        return $this->node1;
    }

    /**
     * @return Node
     */
    public function getNode2()
    {
        return $this->node2;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getData($key = "")
    {
        if($key == "")
            return $this->data;
        else if(array_key_exists($key,$this->data))
            return $this->data[$key];
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @internal param mixed $data
     */
    public function addData($key,$value)
    {
        $this->data[$key] = $value;
    }

    public function getEdgeTime()
    {
        if($this->getData("isWalking"))
            return $this->getData("walkingTime") + $this->getData("waitingTime");
        else
            return $this->getData("travelTime");
    }



}