<?php
/**
 * class Backend_Block_Settings_Cron_List
 * 
 * @package Backend
 * @subpackage Cron
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Settings_Cron_List extends Backend_Block_Template
{
    protected $_config;

    /**
     * Get all the configured cron jobs
     * 
     * @return array 
     */
    public function getCronJobs()
    {
        App_Main::getModel('cron/cron')->initCronJobs();
        return App_Main::getModel('cron/cron')->getJobs();
    }

    /**
     * Get the config value for the path specified
     * 
     * @param string $path
     * @return mixed 
     */
    public function getConfigValue($path)
    {
        return $this->getConfig()->getConfigData($path);
    }
}