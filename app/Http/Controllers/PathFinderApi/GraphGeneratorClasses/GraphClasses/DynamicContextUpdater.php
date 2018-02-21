<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/02/18
 * Time: 21:40
 */
abstract class DynamicContextUpdater
{
    /** @var  $nextUpdater DynamicContextUpdater */
    private $nextUpdater = null;

    /**
     * this function must modify context created by nextUpdater if there is one
     * @return DynamicContext
     */
    abstract public function createContext();

    /**
     * this function must call updateContext of nextUpdater if there is one
     * @param $context DynamicContext
     * @param $edge Edge
     */
    abstract public function updateContext($context,$edge);

    /**
     * @return DynamicContextUpdater
     */
    public function getNextUpdater()
    {
        return $this->nextUpdater;
    }

    /**
     * @param DynamicContextUpdater $nextUpdater
     */
    private function setNextUpdater(DynamicContextUpdater $nextUpdater)
    {
        $this->nextUpdater = $nextUpdater;
    }

    public function addUpdater(DynamicContextUpdater $nextUpdater)
    {
        if($this->getNextUpdater() != null)
            $this->getNextUpdater()->addUpdater($nextUpdater);
        else
            $this->setNextUpdater($nextUpdater);
    }

}