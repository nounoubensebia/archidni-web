<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/02/18
 * Time: 20:50
 */
abstract class DynamicEdgeLoader
{
    private $data;
    /**
     * @var $nextLoader DynamicEdgeLoader
     */
    private $nextLoader;

    /**
     * this function must call loadEdges of nextLoader if there is one
     * @param $context DynamicContext
     * @param $node Node
     */
    abstract public function loadEdges($context,$node);

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return DynamicEdgeLoader
     */
    public function getNextLoader()
    {
        return $this->nextLoader;
    }

    /**
     * @param DynamicEdgeLoader $nextLoader
     */
    private function setNextLoader(DynamicEdgeLoader $nextLoader)
    {
        $this->nextLoader = $nextLoader;
    }

    public function addLoader(DynamicEdgeLoader $nextLoader)
    {
        if($this->getNextLoader() != null)
            $this->getNextLoader()->addLoader($nextLoader);
        else
            $this->setNextLoader($nextLoader);
    }

}