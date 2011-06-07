<?php
/**
 * class Visitor_Block_List
 * 
 * @package Visitor
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Visitor_Block_List extends Backend_Block_List_Abstract
{
    /**
     * Get the visitor list
     * 
     * @return type Core_Model_Object
     */
    public function getResultCollection()
    {
        if(!empty ($this->_collection)) {
            return $this->_collection;
        }
        $_resultCollection = App_Main::getSingleton('visitor/visitor')->getCollection()->getResultCollection();
        if(empty($_resultCollection)) {
            $_resultCollection = array();
        }
        $this->_collection = $_resultCollection;
        return $this->_collection;
    }
    
    public function getBrowserOptionList()
    {
        return array('MSIE 6', 'MSIE 5' , 'MSIE 7', 'MSIE 8', 'Firefox', 'Opera', 'Safari', 'Knopix');
    }

    public function getOsOptionList()
    {
        return array('Windows', 'Linux' , 'Mac');
    }

    public function getMinPageVisitOptionList()
    {
        return array(3,5,8,10,15,20,30,50,100,200);
    }

    public function getRedirectSourceOptionList()
    {
        return array('google.'=>'Google', 'facebook.com'=>'Facebook' , 'twitter.com'=>'Twitter', 'yahoo.'=> 'Yahoo', 'bing.'=> 'Bing');
    }
}
?>
