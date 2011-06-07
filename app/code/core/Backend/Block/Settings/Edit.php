<?php
/**
 * class Backend_Block_Settings_Edit
 * 
 * @package Backend
 * @subpackage Settings
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Settings_Edit extends Backend_Block_Template
{
    protected $_config;
    protected $_website;

    protected function getConfig()
    {
        if(!$this->_config) {
            $this->_config = App_Main::getConfig();
        }
        return $this->_config;
    }

    /**
     * Get the config value for the loaded config object using the path
     * 
     * @param string $path
     * @return mixed 
     */
    public function getConfigValue($path)
    {
        if($this->_website) {
            return $this->_website->getConfig($path);
        }
        return $this->getConfig()->getConfigData($path);
    }

    /**
     * Get the TZ option generated using Zend_Locale
     * 
     * @return array $key=>$value 
     */
    public function getTimezoneOptionList()
    {
        $tzones = Zend_Locale::getTranslationList("territorytotimezone");
        $return = array();
        foreach($tzones as $key=>$value) {
            $return[$key] = $key;
        }
        return $return;
    }


    /**
     * Get the language option list generated using Zend_Locale
     * 
     * @return array $key=>$value 
     */
    public function getLanguageOptionList()
    {
        $languages = Zend_Locale::getTranslationList("language");
        $return = array();
        foreach($languages as $key=>$value) {
            $return[$key] = $value;
        }
        return $return;
    }


    /**
     * Get the countries list generated using Zend_Locale
     * 
     * @return array $key=>$value 
     */
    public function getCountryOptionList()
    {
        return Zend_Locale::getCountryTranslationList();
    }

    /**
     * Get the all website defined within the application
     * 
     * @return array 
     */
    public function getWebsites()
    {
        return App_Main::getModel('core/website')->getWebsites();
    }

    /**
     * Is called from the backend
     * 
     * @return Core_Model_Website
     */
    public function getCurrentWebsite()
    {
        if($this->getRequest()->getParam('website')) {
            $this->_website = App_Main::getModel('core/website')->load($this->getRequest()->getParam('website'));
        }
        return $this->_website; //App_Main::getModel('core/website')->getDefaultWebsite();
    }

    /**
     * Get the theme list for the frontend
     * from the __base-dir__/design/frontend directory
     * 
     * @return array 
     */
    public function getFrontDesignThemeList()
    {
        $themeDir = App_Main::getBaseDir('design'). DS .'frontend';
        if (!$handle = opendir($themeDir)) { return array(); }

        $themes = array();
        $themes['default'] = 'Default';
        while (false !== ($file = readdir($handle))) {
            $exclude = strstr($file, '.') && strpos($file, '.') == 0;
            if($file == 'default' || $exclude || !is_dir($themeDir .DS. $file)) { continue; }
            $themes[$file] = ucfirst($file);
        }

        return $themes;
    }
}