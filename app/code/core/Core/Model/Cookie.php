<?php
/**
 * class Core_Model_Cookie
 * Cookie object model to control frontend browser cookie
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Cookie
{
    const COOKIE_DOMAIN = false;
    const COOKIE_PATH = false;
    const COOKIE_LIFETIME = 3600;
    const COOKIE_HTTPONLY = true;

    protected $_lifetime;

   
    /**
     * Get the global request object
     *
     * @return Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return App_Main::getRequest();
    }

    /**
     * Get the response object
     *
     * @return Core_Controller_Response_Http
     */
    protected function _getResponse()
    {
        return App_Main::getResponse();
    }

    /**
     * Get domain for cookie
     *
     * @return string
     */
    public function getDomain()
    {
        $domain = self::COOKIE_DOMAIN;
        if (empty($domain)) {
            $domain = $this->_getRequest()->getHttpHost();
        }
        return $domain;
    }

    /**
     * Get path for cookie
     *
     * @return string
     */
    public function getPath()
    {
        $path = self::COOKIE_PATH;
        if (empty($path)) {
            $path = $this->_getRequest()->getBasePath();
        }
        return $path;
    }

    /**
     * Get the cookie lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        $lifetime = $this->_lifetime ? $this->_lifetime : App_Main::getWebsite()->getConfig('session-cookie-lifetime');
        return (!is_numeric($lifetime) ? self::COOKIE_LIFETIME : $lifetime);
    }

    /**
     * Set cookie lifetime
     *
     * @param int $lifetime
     * @return Core_Model_Cookie
     */
    public function setLifetime($lifetime)
    {
        $this->_lifetime = (int)$lifetime;
        return $this;
    }

    /**
     * Get use HTTP only flag
     *
     * @return bool
     */
    public function getHttponly()
    {
        $httponly = self::COOKIE_HTTPONLY;
        if (is_null($httponly)) {
            return null;
        }
        return (bool)$httponly;
    }

    /**
     * Is https secure request
     * Use secure on backend only
     *
     * @return bool
     */
    public function isSecure()
    {
        if (App_Main::isRouterAdmin()) {
            return $this->_getRequest()->isSecure();
        }
        return false;
    }

    /**
     * Set cookie
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @return Core_Model_Cookie
     */
    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        /**
         * Check headers sent
         */
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if ($period === true) {
            $period = 3600 * 24 * 365;
        } elseif (is_null($period)) {
            $period = $this->getLifetime();
        }

        if ($period == 0) {
            $expire = 0;
        } else {
            $expire = time() + $period;
        }
        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

        return $this;
    }

    /**
     * Get cookie or false if not exists
     *
     * @param string $neme The cookie name
     * @return mixed
     */
    public function get($name = null)
    {
        return $this->_getRequest()->getCookie($name, false);
    }

    /**
     * Delete cookie
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param int|bool $httponly
     * @return Core_Model_Cookie
     */
    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        // Check headers sent
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, null, null, $path, $domain, $secure, $httponly);
        return $this;
    }
}
?>