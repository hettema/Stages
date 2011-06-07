<?php
/**
 * class Core_Model_Translate
 * Translation wraper object for the Zend_Translate_Adapter
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Translate
{
    const CSV_SEPARATOR     = ',';
    const SCOPE_SEPARATOR   = '::';
    const CACHE_TAG         = 'translate';

    const CONFIG_KEY_AREA   = 'area';
    const CONFIG_KEY_LOCALE = 'locale';
    const CONFIG_KEY_WEBSITE = 'website';
    const CONFIG_KEY_DESIGN_PACKAGE = 'package';
    const CONFIG_KEY_DESIGN_THEME   = 'theme';

    protected $_locale;

    protected $_translate;

    protected $_config;

    public function __construct()
    {
    }

    /**
     * Initialization translation data
     *
     * @param   string $area
     * @return  Core_Model_Translate
     */
    public function init($area, $forceReload = false)
    {

        $this->setConfig(array(self::CONFIG_KEY_AREA=>$area));
        $this->initTranslate();
        $this->_loadThemeTranslation();
        return $this;
    }

    /**
     * Retrieve translation object
     *
     * @return Zend_Translate_Adapter
     */
    public function initTranslate($forceReload = false)
    {
        if (is_null($this->_translate) || $forceReload) {
            $this->_translate = App_Main::getModel('core/translate_csv', array(App_Main::getBaseDir('locale'), $this->getLocale()));
        }

        return $this->_translate;
    }

    public function getTranslate()
    {
        return $this->_translate;
    }

     /**
     * Loading current theme translation
     *
     * @return Core_Model_Translate
     */
    protected function _loadThemeTranslation($forceReload = false)
    {
        $this->getTranslate()->addTranslation(App_Main::getDesign()->getLocaleFileName(false));
        return $this;
    }
    
    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  Core_Model_Translate
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config[self::CONFIG_KEY_LOCALE])) {
            $this->_config[self::CONFIG_KEY_LOCALE] = $this->getLocale();
        }
        if (!isset($this->_config[self::CONFIG_KEY_WEBSITE])) {
            $this->_config[self::CONFIG_KEY_WEBSITE] = App_Main::getWebsite()->getId();
        }
        return $this;
    }

    /**
     * Retrieve config value by key
     *
     * @param   string $key
     * @return  mixed
     */
    public function getConfig($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return null;
    }
    
    /**
     *
     * @return string locale 
     */
    public function getLocale()
    {
        if (is_null($this->_locale)) {
            $this->_locale = App_Main::getWebsite()->getLocale();
        }
        return $this->_locale;
    }

    /**
     *
     * @param string $locale
     * @return Core_Model_Translate 
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Translate the string with the passed arguments
     * The first element of the array is choosen as the strin and the rest all 
     * are considered as the variable substitutes for '%s' in the string
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
        $text = array_shift($args);
        if (empty($text) || (is_object($text) && '' == $text->getText())) { return ''; }

        $translated =  $this->getTranslate()->_($text, $this->getLocale());
        $result = @vsprintf($translated, $args);
        if ($result === false) {
            $result = $translated;
        }

        if ($result === false){
            $result = $translated;
        }

        return $result;
    }
}
