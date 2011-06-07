<?php
/**
 * class Core_Model_Session_Absctract
 * Abstract class for the session object
 * 
 * @package Core
 * @subpackage Session
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Session_Abstract extends Core_Model_Abstract
{
    const VALIDATOR_KEY                         = '_session_validator_data';
    const VALIDATOR_HTTP_USER_AGENT_KEY         = 'http_user_agent';
    const VALIDATOR_HTTP_X_FORVARDED_FOR_KEY    = 'http_x_forwarded_for';
    const VALIDATOR_HTTP_VIA_KEY                = 'http_via';
    const VALIDATOR_REMOTE_ADDR_KEY             = 'remote_addr';

    /**
     * Conigure and start session
     *
     * @param string $sessionName
     * @return Core_Model_Session_Abstract
     */
    public function start($sessionName=null)
    {
        if (isset($_SESSION)) {
            return $this;
        }

        if (is_writable($this->getSessionSavePath())) {
            session_save_path($this->getSessionSavePath());
        }

        switch($this->getSessionSaveMethod()) {
            case 'db':
                ini_set('session.save_handler', 'user');
                $sessionResource = App_Main::getResourceSingleton('core/session');
                $sessionResource->setSaveHandler();
            break;
            case 'memcache':
                ini_set('session.save_handler', 'memcache');
                session_save_path($this->getSessionSavePath());
            break;
            default:
                session_module_name('files');
            break;
        }

        /*if ($sessionName == 'backend') {
            $adminSessionLifetime = App_Main::SESSION_ADMIN_LIFETIME;
            if ($adminSessionLifetime > 60) {
                App_Main::getSingleton('core/cookie')->setLifetime($adminSessionLifetime);
            }
        }*/

        // set session cookie params
        session_set_cookie_params(
            $this->getCookie()->getLifetime(),
            $this->getCookie()->getPath(),
            $this->getCookie()->getDomain(),
            $this->getCookie()->isSecure(),
            $this->getCookie()->getHttponly()
        );

        /*tmp vers */
           $a =  $this->getCookie()->getLifetime();
           $b =  $this->getCookie()->getPath();
           $c =  $this->getCookie()->getDomain();
           $d =  $this->getCookie()->isSecure();
           $e =  $this->getCookie()->getHttponly();


        if (!empty($sessionName)) {
            $this->setSessionName($sessionName);
        }

        // potential custom logic for session id (ex. switching between hosts)
        $this->setSessionId();


        /*if ($sessionCacheLimiter = App_Main::SESSION_CACHE_LIMITER) {
            session_cache_limiter((string)$sessionCacheLimiter);
        }*/

        session_start();

        return $this;
    }

    /**
     * Retrieve cookie object
     *
     * @return Core_Model_Cookie
     */
    public function getCookie()
    {
        return App_Main::getSingleton('core/cookie');
    }

    /**
     * Init session with namespace
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Core_Model_Session_Abstract
     */
    public function init($namespace, $sessionName=null)
    {
        if (!isset($_SESSION)) {
            $this->start($sessionName);
        }
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }

        $this->_data = &$_SESSION[$namespace];

        $this->validate();
        $this->revalidateCookie();

        return $this;
    }

    /**
     * Additional get data with clear mode
     *
     * @param string $key
     * @param bool $clear
     * @return mixed
     */
    public function getData($key='', $clear = false)
    {
        $data = parent::getData($key);
        if ($clear && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return $data;
    }

    /**
     * Retrieve session Id
     *
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Set custom session id
     *
     * @param string $id
     * @return Core_Model_Session_Abstract
     */
    public function setSessionId($id=null)
    {
        if (!is_null($id) && preg_match('#^[0-9a-zA-Z,-]+$#', $id)) {
            session_id($id);
        }
        return $this;
    }

    /**
     * Retrieve session name
     *
     * @return string
     */
    public function getSessionName()
    {
        return session_name();
    }

    /**
     * Set session name
     *
     * @param string $name
     * @return Core_Model_Session_Abstract
     */
    public function setSessionName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Unset all data
     *
     * @return Core_Model_Session_Abstract
     */
    public function unsetAll()
    {
        $this->unsetData();
        return $this;
    }

    /**
     * Alias for unsetAll
     *
     * @return Core_Model_Session_Abstract
     */
    public function clear()
    {
        return $this->unsetAll();
    }

    /**
     * Retrieve session save method
     * Default files
     *
     * @return string
     */
    public function getSessionSaveMethod()
    {
        return 'files';
    }

    /**
     * Get sesssion save path
     *
     * @return string
     */
    public function getSessionSavePath()
    {
        return App_Main::getBaseDir('session');
    }

    /**
     * Use REMOTE_ADDR in validator key
     *
     * @return bool
     */
    public function useValidateRemoteAddr()
    {
        return true;
    }

    /**
     * Use HTTP_VIA in validator key
     *
     * @return bool
     */
    public function useValidateHttpVia()
    {
        return true;
    }

    /**
     * Use HTTP_X_FORWARDED_FOR in validator key
     *
     * @return bool
     */
    public function useValidateHttpXForwardedFor()
    {
        return true;
    }

    /**
     * Use HTTP_USER_AGENT in validator key
     *
     * @return bool
     */
    public function useValidateHttpUserAgent()
    {
        return true;
    }

    /**
     * Retrieve skip User Agent validation strings (Flash etc)
     *
     * @return array
     */
    public function getValidateHttpUserAgentSkip()
    {
        return array();
    }

    /**
     * Validate session
     *
     * @param string $namespace
     * @return Core_Model_Session_Abstract
     */
    public function validate()
    {
        if (!isset($this->_data[self::VALIDATOR_KEY])) {
            $this->_data[self::VALIDATOR_KEY] = $this->getValidatorData();
        }
        else {
            if (!$this->_validate()) {
                $this->getCookie()->delete(session_name());
                return false;
                // throw core session exception
                //throw new Core_Model_Session_Exception('');
            }
        }

        return $this;
    }

    /**
     * Validate data
     *
     * @return bool
     */
    protected function _validate()
    {
        $sessionData = $this->_data[self::VALIDATOR_KEY];
        $validatorData = $this->getValidatorData();

        if ($this->useValidateRemoteAddr() && $sessionData[self::VALIDATOR_REMOTE_ADDR_KEY] != $validatorData[self::VALIDATOR_REMOTE_ADDR_KEY]) {
            return false;
        }
        if ($this->useValidateHttpVia() && $sessionData[self::VALIDATOR_HTTP_VIA_KEY] != $validatorData[self::VALIDATOR_HTTP_VIA_KEY]) {
            return false;
        }
        if ($this->useValidateHttpXForwardedFor() && $sessionData[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY] != $validatorData[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY]) {
            return false;
        }
        if ($this->useValidateHttpUserAgent()
            && $sessionData[self::VALIDATOR_HTTP_USER_AGENT_KEY] != $validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY]
            && !in_array($validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY], $this->getValidateHttpUserAgentSkip())) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve unique user data for validator
     *
     * @return array
     */
    public function getValidatorData()
    {
        $parts = array(
            self::VALIDATOR_REMOTE_ADDR_KEY             => '',
            self::VALIDATOR_HTTP_VIA_KEY                => '',
            self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY    => '',
            self::VALIDATOR_HTTP_USER_AGENT_KEY         => ''
        );

        // collect ip data
        if (App_Main::getHelper('core/http')->getRemoteAddr()) {
            $parts[self::VALIDATOR_REMOTE_ADDR_KEY] =App_Main::getHelper('core/http')->getRemoteAddr();
        }
        if (isset($_ENV['HTTP_VIA'])) {
            $parts[self::VALIDATOR_HTTP_VIA_KEY] = (string)$_ENV['HTTP_VIA'];
        }
        if (isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $parts[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY] = (string)$_ENV['HTTP_X_FORWARDED_FOR'];
        }

        // collect user agent data
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $parts[self::VALIDATOR_HTTP_USER_AGENT_KEY] = (string)$_SERVER['HTTP_USER_AGENT'];
        }

        return $parts;
    }


    /**
     * Revalidate cookie
     *
     * @return Core_Model_Session_Abstract
     */
    public function revalidateCookie()
    {
        if (!$this->getCookie()->getLifetime()) {
            return $this;
        }
        if (empty($_SESSION['_cookie_revalidate'])) {
            $time = time() + round($this->getCookie()->getLifetime() / 4);
            $_SESSION['_cookie_revalidate'] = $time;
        }
        else {
            if ($_SESSION['_cookie_revalidate'] < time()) {
                if (!headers_sent()) {
                    $this->getCookie()->set(session_name(), session_id());

                    $time = time() + round($this->getCookie()->getLifetime() / 4);
                    $_SESSION['_cookie_revalidate'] = $time;
                }
            }
        }

        return $this;
    }
}
?>
