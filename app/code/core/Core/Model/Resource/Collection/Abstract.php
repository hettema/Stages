<?php
/**
 * class Core_Model_Resource_Collection_Abstract
 * Abstract resource collection model
 * 
 * @package Core
 * @category Resource-Collection-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
abstract class Core_Model_Resource_Collection_Abstract  extends Core_Model_Resource_Abstract
{
    /**
     *
     * @var Core_Model_Resource_Abstract 
     */
    private $_resource;
    /**
     * Primary filter attributes, unset or restore all other filter if any of this is changed
     *
     * @var array 
     */
    private $_primaryFilters = array();

    public function __construct(Core_Model_Resource_Abstract $resource)
    {
        $this->_setResource($resource);
        $this->_construct();
    }
    
    /**
     * Set the resource model instance
     * 
     * @param Core_Model_Resource_Abstract $resource
     * @return Core_Model_Resource_Collection_Abstract 
     */
    protected function _setResource($resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    /**
     *
     * @return Core_Model_Resource_Abstract 
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * Get the request object
     *
     * @return Core_Controller_Request_Http 
     */
    protected function _getRequest()
    {
        if(!empty($this->_request)) {
            return $this->_request;
        }

        $this->_request = App_Main::getRequest();
        return $this->_request;
    }

    /**
     * Set the filter attributes into the object
     * These filters will be used while getting the resultCollection
     *
     * @param array $runtimeFilters
     * @return Core_Model_Resource_Collection_Abstract 
     */
    public function setFilters(array $runtimeFilters = array())
    {
        $_request = $this->_getRequest();
        $sessionVar = $this->_getSessionVariable();
        $_set_filter = true;

        foreach ($this->filters as $filter=>$value) {

            //$this->getFinalFilterValue();
            if(!empty($runtimeFilters[$filter]) || $_request->getParam($filter)) {

                if(!$_set_filter) { // contunue if  it is not allowed to set any further filter values
                    $_request->setParam($filter, null); continue;
                }
                
                $newValue = !empty($runtimeFilters[$filter]) ? $runtimeFilters[$filter] : $_request->getParam($filter);

                if(in_array($filter, $this->_primaryFilters) && $value != $newValue) {
                    $_set_filter = false;
                }
                $this->filters[$filter] = $newValue;
                
                /*if($filter == 'orderBy') {
                    $this->filters['order'] = 'ASC';
                    if(!empty($_SESSION[$sessionVar]['filters'][$filter]) && $_SESSION[$sessionVar]['filters'][$filter] == $this->filters[$filter]) {
                        $this->filters['order'] = $_SESSION[$sessionVar]['filters']['order'] == 'ASC' ? 'DESC' : 'ASC';
                    }
                }*/
            } elseif (isset($_SESSION[$sessionVar]['filters'][$filter])) {
                //$this->filters[$filter] = $_SESSION[$sessionVar]['filters'][$filter];
            }
        }
        //$this->setFiltersSession();
        return $this;
    }

    /**
     * Set a filter attribute value
     *
     * @param string $filter
     * @param mixed $value
     * @return Core_Model_Resource_Collection_Abstract 
     */
    public function setFilter($filter, $value)
    {
       $this->filters[$filter] = $value;
       return $this;
    }

    /**
     * Get the session variable string, used for saving the filter data in session specific to the object instance
     *
     * @return mixed 
     */
    private function _getSessionVariable()
    {
        $className = get_class($this);
        return $className;

        # @to Do.. make it more session variable orineted and associate the class object for session
        if(empty($_SESSION[$className])) {
            session_register($className);
            return $_SESSION[$className];
        } else {
            return $_SESSION[$className];
        }
        return false;
    }

    
    /**
     * @todo sessions are not implemented.. alter this function to store the filter data in session
     * 
     * @return Core_Model_Resource_Collection_Abstract 
     */
    private function setFiltersSession()
    {
        if(!empty($this->filters)) {
            $sessionVar = $this->_getSessionVariable();
            session_register($sessionVar);
            $_SESSION[$sessionVar]['filters']= $this->filters;
            unset($_SESSION[$sessionVar]['filters']['page']);
        }
        return $this;
    }
    
    /**
     * Get the filter value 
     *
     * @param string $filter
     * @return mixed 
     */
    public function getFilterValue($filter)
    {
        if(isset($this->filters[$filter])) {
            return $this->filters[$filter];
        }
        return false;
    }
}
?>
