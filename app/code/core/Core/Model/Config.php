<?php
/**
 * class Core_Model_Config
 * Object to store and retrive the global/website-specific configuration 
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Config extends Core_Model_Abstract
{

    protected $_config;
    
    protected function _construct()
    {
        $this->_init('core/config');
        //Load config is not loaded already
        if(!$this->hasData()) {
            $this->loadConfig() ;
        }
    }

    /**
     * Load the config data from the DB
     */
    protected function loadConfig()
    {
        $this->getResource()->loadConfigData($this);

        if(!$this->getSecurePaths()) {
            $this->setData('secure_paths', array());
        }
    }

    /**
     * Check whether the path is served via a secure server protocol
     * 
     * @param string $path
     * @return bool 
     */
    public function shouldUrlBeSecure($path)
    {
        return in_array($path, $this->getConfig('securePaths'));
    }

    /**
     * Get the config calue for the specified path
     * 
     * @param string $path
     * @return string 
     */
    public function getConfigData($path)
    {
        return $this->getData($path);
    }

    /**
     * Save the config data
     * 
     * @param array $configValues
     * @param string $scope
     * @param int $scopeId
     * @return Core_Model_Config 
     */
    public function saveConfigData($configValues = array(), $scope = 'default', $scopeId = 0)
    {
        foreach($configValues as $path=>$value) {
            $this->deleteConfig($path, $scope, $scopeId);
            if (is_array($value)) {
                $value = implode("," , $value);
            }
            $this->saveConfig($path, $value, $scope, $scopeId);
        }
        return $this;
    }

    /**
     * Save config value to DB
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Core_Config
     */
    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        $this->getResource()->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);

        return $this;
    }

    /**
     * Delete config value from DB
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return  Core_Model_Config
     */
    public function deleteConfig($path, $scope = 'default', $scopeId = 0)
    {
        $this->getResource()->deleteConfig(rtrim($path, '/'), $scope, $scopeId);
        return $this;
    }

    /**
     * Load the website specific config with fall back to global config
     * 
     * @param int $websiteId
     * @return Core_Model_Config
     */
    public function loadWebsiteConfig($websiteId)
    {
        // return the default config is website id is empty. 
        if(empty ($websiteId)) { 
            return $this; 
        }
        
        $config = App_Main::getModel('core/config', $this->getData());
        $this->_getResource()->loadConfigData($config, 'website', $websiteId);
        return $config;
    }

    /**
     * Load the scope id from the scope, path and value
     * Called from the website object to load the webiste from the domain
     * 
     * @param int $scope
     * @param string $path
     * @param string $value
     * @return int scope id 
     */
    public function getScopeFromPathValue($scope, $path, $value)
    {
        return $this->_getResource()->getScopeIdFromPathValue($scope, $path, $value);
    }
}
?>
