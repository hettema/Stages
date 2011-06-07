<?php
/**
 * class Admin_Block_User_List
 * 
 * @package Admin
 * @subpackage User
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Block_User_List extends Backend_Block_Template
{
    protected $_collection = array();
   
    public function __construct()
    {
        
    }

    /**
     * Get the admin users collection array
     * 
     * @return array 
     */
    public function getResultCollection()
    {
        if(!empty ($this->_collection)) {
            return $this->_collection;
        }
        $_resultCollection = App_Main::getSingleton('admin/user')->getCollection()->getResultCollection();
        if(empty($_resultCollection)) {
            $_resultCollection = array();
        } 
        $this->_collection = $_resultCollection;
        return $this->_collection;
    }    
}
?>