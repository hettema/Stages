<?php
/**
 * class Core_Controller_Router_Abstract
 * Abstract class for the router objects
 * 
 * @package Core
 * @category Controller-Router
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */abstract class Core_Controller_Router_Abstract
{
    protected $_front;

    /**
     * Set front area 
     * - frontend, backend
     * @param string $front
     * @return Core_Controller_Router_Abstract 
     */
    public function setFront($front)
    {
        $this->_front = $front;
        return $this;
    }

    /**
     *
     * @return string front area 
     */
    public function getFront()
    {
        return $this->_front;
    }

    /**
     * @todo modify this function to configure the front name of the route
     * 
     * @param string $routeName
     * @return string route name 
     */
    public function getFrontNameByRoute($routeName)
    {
        return $routeName;
    }

    public function getRouteByFrontName($frontName)
    {
        return $frontName;
    }

    abstract public function match(Zend_Controller_Request_Http $request);
}