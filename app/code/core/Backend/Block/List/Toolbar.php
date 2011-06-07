<?php
/**
 * class Backend_Block_List_Toolbar
 * Toolbar block to show pagination for a set of object colection
 * 
 * @package Backend
 * @subpackage List
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_List_Toolbar extends Backend_Block_Template
{
    protected $_collection;

    public function _construct()
    {
        $this->setTemplate('list/toolbar.phtml');
    }

    /**
     * Set the result collection array of object models
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Get the result collection
     * 
     * @return array
     */
    public function _getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get the total count of results
     * 
     * @return int
     */
    public function getTotalCount()
    {
        $count = $this->_getCollection()->getTotalCount();
        return !empty($count) ? $count : 0;
    }

    /**
     * Get the query limit used for the current result collection
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->_getCollection()->getLimit();
    }

    /**
     * Get the current page of the result collection
     * 
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_getCollection()->getPage();
    }

    /**
     * Generic method to get the page url for a paginated list
     * 
     * @return string
     */
    public function getPageUrl($p)
    {
        $request = App_Main::getRequest();
        $params = $request->getQuery();

        $params['page'] = $p;
        return $this->getUrl('*/*/*', array('_use_rewrite'=>true, '_query'=>$params));
    }
}
?>
