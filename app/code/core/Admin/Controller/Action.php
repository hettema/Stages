<?php
/**
 * class Admin_Controller_Action
 * 
 * @package Admin
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Controller_Action extends Core_Controller_Action
{
    /**
     * Currently used area
     *
     */
    protected $_currentArea = 'backend';
    
    /**
     * Get the admin session
     * 
     * @return Admin_Model_Session 
     */
    public function _getSession()
    {
        return App_Main::getAdminSession();
    }
}
?>
