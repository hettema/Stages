<?php
/**
 * class Core_Model_Website
 * The system will load website specific to the requested host or default if not specified
 * This can have completely different configuration values like theme, locale, meta data, timezone, google analytics id etc.
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Website extends Core_Model_Abstract
{
    const URL_TYPE_WEB                  = 'web';
    const URL_TYPE_LINK                 = 'link';
    const URL_TYPE_SKIN                 = 'skin';
    const URL_TYPE_JS                   = 'js';
    const URL_TYPE_MEDIA                = 'media';

    /**
     *
     * @var Core_Model_Config 
     */
    protected $_config;
    
    /**
     *
     * @var string locale 
     */
    protected $_locale;
    
    /**
     *
     * @var string theme 
     */
    protected $_theme;
    
    protected function _construct()
    {
        $this->_init('core/website');

    }

    /**
     * Load default website
     * 
     * @return Core_Model_Website
     */
    public function loadDefaultWebsite()
    {
        return $this->_getResource()->loadDefaultWebsite($this);
    }


    /**
     * Load the website based on the http host, If the host is not found, the default website is loaded
     *
     * @return Core_Model_Website
     */
    public function loadFromRequestUrl()
    {
        $request = App_Main::getRequest();
        $httpHost = $request->getHttpHost();
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $websiteId = App_Main::getConfig()->getScopeFromPathValue('website', 'web-secure-base-url', 'https://'. $httpHost);
        } else {
            $websiteId = App_Main::getConfig()->getScopeFromPathValue('website', 'web-unsecure-base-url', 'http://'. $httpHost);
        }

        if(!empty($websiteId)) {
            return $this->load($websiteId);
        } else {
            return $this->loadDefaultWebsite();
        }
    }

    /**
     * Get defualt website that is configured
     *
     * 
     * @return Core_Model_Website
     */
    public function getDefaultWebsite()
    {
        return $this->_getResource()->getDefaultWebsite();
    }

    /**
     * Get all teh defined websites
     * 
     * @return array websites 
     */
    public function getWebsites()
    {
        return $this->_getResource()->getWebsites();
    }

    /**
     * Validate the website code prior to adding a new website
     * 
     * @param string $code
     * @return bool 
     */
    public function validateWebsiteCode($code)
    {
        return $this->_getResource()->validateWebsiteCode($code);
    }

    /**
     * Get the config model for the website or default config model if not found
     * 
     * @param type $path
     * @return Core_Model_Config | string value 
     */
    public function getConfig($path = null)
    {
        if(!$this->_config) {
            $this->_config = App_Main::getConfig()->loadWebsiteConfig($this->getId());
        }
        if(!empty ($path)) {
            return $this->_config->getConfigData($path);
        }
        return $this->_config;
    }

    /**
     * Get the default locale configured for the website
     * 
     * @return string 
     */
    public function getLocale()
    {
        if(!empty($this->_locale)) {
            return $this->_locale;
        }
        
        $this->_locale =  $this->getConfig('web-default-locale') ? $this->getConfig('web-default-locale') : App_Main::DEFAULT_LOCALE;
        return $this->_locale;
    }

    /**
     * Get the theme for the website
     * 
     * @return string
     */
    public function getTheme()
    {
         if(!empty($this->_theme)) {
            return $this->_theme;
        }
        $this->_theme = $this->getConfig('web-design-default-theme') ? $this->getConfig('web-design-default-theme') : 'default';
        
        return $this->_theme;
    }

     /**
     * Returns the base url confogurd for the current website, if not return the default url
     *
     * @param string $type
     * @param bool $secure
     * @return string url
     */
    public function getBaseUrl($type=self::URL_TYPE_LINK, $secure=null)
    {
        $cacheKey = $type.'/'.(is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            switch ($type) {
                case self::URL_TYPE_WEB:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web-'.($secure ? 'secure' : 'unsecure').'-base-url');
                    break;

                case self::URL_TYPE_LINK:
                    $secure = (bool)$secure;
                    $url = $this->getConfig('web-'.($secure ? 'secure' : 'unsecure').'-base-url');
                    break;

                case self::URL_TYPE_SKIN:
                case self::URL_TYPE_MEDIA:
                case self::URL_TYPE_JS:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web-'.($secure ? 'secure' : 'unsecure').'-base-'.$type.'-url');
                    break;

                default:
                    throw App_Main::exception('Core', App_Main::getHelper('core')->__('Invalid base url type'));
            }

            //load the default url from App_Main if the url is not set
            if(empty ($url)) {
                $url = !$secure ? SERVER_URI : SECURE_SERVER_URI;
            }

            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/').'/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }
    
    /**
     * Check whether the current request is secure
     * 
     * @return bool 
     */
    public function isCurrentlySecure()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            return true;
        }

        $secureBaseUrl = $this->getConfig('web-secure-base-url');
        if (!$secureBaseUrl) {
            return false;
        }

        $uri = Zend_Uri::factory($secureBaseUrl);
        $isSecure = ($uri->getScheme() == 'https' ) && isset($_SERVER['SERVER_PORT']) && ($uri->getPort() == $_SERVER['SERVER_PORT']);
        return $isSecure;
    }

}
?>
