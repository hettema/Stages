<?php
/**
 * class Core_Model_Sesison
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Session extends Core_Model_Session_Abstract
{
    const SESSION_SAVE_METHOD  = 'db';
    const SESSION_SAVE_PATH    = false;

    const USE_VALIDATE_REMOTE_ADDR      = false;
    const USE_VALIDATE_HTTP_VIA         = true;
    const USE_VALIDATE_X_FORWARDED      = false;
    const USE_VALIDATE_USER_AGENT       = false;

    const USET_AGENT_SKIP      = true;
    const LOG_EXCEPTION_FILE   = 'exception.log';

    const SESSION_ID_QUERY_PARAM        = 'SID';
    
    function __construct($data=array())
    {
        $name = isset($data['name']) ? $data['name'] : null;
        $this->init('core', $name);
    }
    
    /**
     * Init session
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Core_Model_Session
     */
    public function init($namespace, $sessionName=null)
    {
        parent::init($namespace, $sessionName);
        $this->addHost(true);
        return $this;
    }

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->getCookie()->getDomain();
    }

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->getCookie()->getPath();
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->getCookie()->getLifetime();
    }

    /**
     * Use REMOTE_ADDR in validator key
     *
     * @return bool
     */
    public function useValidateRemoteAddr()
    {
        $use = self::USE_VALIDATE_REMOTE_ADDR;
        if (is_null($use)) {
            return parent::useValidateRemoteAddr();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_VIA in validator key
     *
     * @return bool
     */
    public function useValidateHttpVia()
    {
        $use = self::USE_VALIDATE_HTTP_VIA;
        if (is_null($use)) {
            return parent::useValidateHttpVia();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_X_FORWARDED_FOR in validator key
     *
     * @return bool
     */
    public function useValidateHttpXForwardedFor()
    {
        $use = self::USE_VALIDATE_X_FORWARDED;
        if (is_null($use)) {
            return parent::useValidateHttpXForwardedFor();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_USER_AGENT in validator key
     *
     * @return bool
     */
    public function useValidateHttpUserAgent()
    {
        $use = self::USE_VALIDATE_USER_AGENT;
        if (is_null($use)) {
            return parent::useValidateHttpUserAgent();
        }
        return (bool)$use;
    }

    /**
     * Retrieve skip User Agent validation strings (Flash etc)
     *
     * @return array
     */
    public function getValidateHttpUserAgentSkip()
    {
        $userAgents = array();
        $skip = self::USER_AGENT_SKIP;
        foreach ($skip->children() as $userAgent) {
            $userAgents[] = (string)$userAgent;
        }
        return $userAgents;
    }

    /**
     * Retrieve messages from session
     *
     * @param   bool $clear
     * @return  Core_Model_Message_Collection
     */
    public function getMessages($clear=false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages(App_Main::getModel('core/message_collection'));
        }

        if ($clear) {
            $messages = clone $this->getData('messages');
            $this->getData('messages')->clear();
            return $messages;
        }
        return $this->getData('messages');
    }

    /**
     * Not Main exeption handling
     *
     * @param   Exception $exception
     * @param   string $alternativeText
     * @return  Core_Model_Session
     */
    public function addException(Exception $exception, $alternativeText, $showErrorDetail = false)
    {
        // log exception to exceptions log
        $message = sprintf('Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            $exception->getTraceAsString());
        App_Main::log($message, Zend_Log::DEBUG, self::LOG_EXCEPTION_FILE);
        
        $sessionError = $alternativeText. ($showErrorDetail ? ' :: '. $exception->getMessage() : '');
        $this->addMessage(App_Main::getSingleton('core/message')->error($sessionError));
        return $this;
    }

    /**
     * Adding new message to message collection
     *
     * @param   Core_Model_Message_Abstract $message
     * @return  Core_Model_Session
     */
    public function addMessage(Core_Model_Message_Abstract $message)
    {
        $this->getMessages()->add($message);
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  Core_Model_Session
     */
    public function addError($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->error($message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  Core_Model_Session
     */
    public function addWarning($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->warning($message));
        return $this;
    }

    /**
     * Adding new nitice message
     *
     * @param   string $message
     * @return  Core_Model_Session
     */
    public function addNotice($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->notice($message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  Core_Model_Session
     */
    public function addSuccess($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->success($message));
        return $this;
    }

    /**
     * Adding messages array to message collection
     *
     * @param   array $messages
     * @return  Core_Model_Session
     */
    public function addMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        return $this;
    }

    /**
     * Specify session identifier
     *
     * @param   string|null $id
     * @return  Core_Model_Session
     */
    public function setSessionId($id=null)
    {
        if (is_null($id)) {
            $_queryParam = $this->getSessionIdQueryParam();
            if (isset($_GET[$_queryParam])) {
                $id = $_GET[$_queryParam];
            }
        }

        $this->addHost(true);
        return parent::setSessionId($id);
    }

    public function getSessionIdQueryParam()
    {
        return self::SESSION_ID_QUERY_PARAM;
    }
    
    /**
     * Add hostname to session
     *
     * @param string $host
     * @return Core_Model_Session
     */
    public function addHost($host)
    {
        if ($host === true) {
            if (!$host = App_Main::getControllerFrontend()->getRequest()->getHttpHost()) {
                return $this;
            }
        }

        if (!$host) {
            return $this;
        }

        $hosts = $this->getSessionHosts();
        $hosts[$host] = true;
        $this->setSessionHosts($hosts);
        return $this;
    }

    /**
     * Retrieve session save method
     *
     * @return string
     */
    public function getSessionSaveMethod()
    {
        if (self::SESSION_SAVE_METHOD) {
            return self::SESSION_SAVE_METHOD;
        }
        return parent::getSessionSaveMethod();
    }

    /**
     * Get sesssion save path
     *
     * @return string
     */
    public function getSessionSavePath()
    {
        if (self::SESSION_SAVE_PATH) {
            return self::SESSION_SAVE_PATH;
        }
        return parent::getSessionSavePath();
    }

    /**
     * Get Session Form Key
     *
     * @return string 16 bit unique key for forms
     */
    public function getFormKey()
    {
        if (!$this->getData('_form_key')) {
            $this->setData('_form_key', App_Main::getHelper('core')->getRandomString(16));
        }
        return $this->getData('_form_key');
    }
}
?>
