<?php
/**
 * class Backend_Model_Url
 * 
 * @package Core
 * @subpackage Eav
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Model_Url extends Core_Model_Url
{
    /**
     * Get the secure mode for backend
     *
     * @return bool
     */
    public function getSecure()
    {
        return App_Main::getConfig('web-secure-backend');
    }
}
