<?php
/**
 * class Backend_Block_Template
 * 
 * @package Backend
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Template extends Core_Block_Template
{
    /**
     * 
     *
     * @return string
     */
    protected function _getUrlModelClass()
    {
        return 'backend/url';
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return App_Main::getSession()->getFormKey();
    }

    /**
     * Check whether or not the module output is enabled
     *
     * @param string $moduleName Full module name
     * @return boolean
     */
    public function isOutputEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->getModuleName();
        }
        return !App_Main::getConfig('advanced-modules-disable-output-' . $moduleName);
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
