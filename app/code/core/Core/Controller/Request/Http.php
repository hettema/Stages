<?php
/**
 * class Core_Controller_Request_Http
 * 
 * @package Core
 * @category Controller-Request
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Request_Http extends Zend_Controller_Request_Http
{
    /**
     * ORIGINAL_PATH_INFO
     * @var string
     */
    protected $_originalPathInfo= '';
    protected $_requestString   = '';

    /**
     * Path info array used before applying rewrite from config
     *
     * @var null || array
     */
    protected $_rewritedPathInfo= null;
    protected $_requestedRouteName = null;

    protected $_route;

    protected $_directFrontNames = array();
    protected $_controllerModule = null;

    public function __construct($uri = null)
    {
        parent::__construct($uri);
        $names = array(); //direct front names
        if ($names) {
            $this->_directFrontNames = $names->asArray();
        }
    }

    /**
     * Returns ORIGINAL_PATH_INFO.
     * This value is calculated instead of reading PATH_INFO
     * directly from $_SERVER due to cross-platform differences.
     *
     * @return string
     */
    public function getOriginalPathInfo()
    {
        if (empty($this->_originalPathInfo)) {
            $this->setPathInfo();
        }
        return $this->_originalPathInfo;
    }

    /**
     * Get the website from the request path
     * 
     * @return Core_Model_Website 
     */
    public function getWebsiteFromPath()
    {
        if (!$this->_websiteCode) {
            $this->_websiteCode = App_Main::getWebsite()->getCode();
        }
        return $this->_websiteCode;
    }

    /**
     * Set the PATH_INFO string
     * Set the ORIGINAL_PATH_INFO string
     *
     * @param string|null $pathInfo
     * @return Zend_Controller_Request_Http
     */
    public function setPathInfo($pathInfo = null)
    {
        if ($pathInfo === null) {
            $requestUri = $this->getRequestUri();
            if (null === $requestUri) {
                return $this;
            }

            // Remove the query string from REQUEST_URI
            $pos = strpos($requestUri, '?');
            if ($pos) {
                $requestUri = substr($requestUri, 0, $pos);
            }

            $baseUrl = $this->getBaseUrl();
            $pathInfo = substr($requestUri, strlen($baseUrl));

            if ((null !== $baseUrl) && (false === $pathInfo)) {
                $pathInfo = '';
            } elseif (null === $baseUrl) {
                $pathInfo = $requestUri;
            }
            
            $this->_originalPathInfo = (string) $pathInfo;

            $this->_requestString = $pathInfo . ($pos!==false ? substr($requestUri, $pos) : '');
        }

        $this->_pathInfo = (string) $pathInfo;
        return $this;
    }

    /**
     * Specify new path info
     * called when a rewrite config is loaded
     *
     * @param   string $pathInfo
     * @return  Core_Controller_Request_Http
     */
    public function rewritePathInfo($pathInfo)
    {
        if (($pathInfo != $this->getPathInfo()) && ($this->_rewritedPathInfo === null)) {
            $this->_rewritedPathInfo = explode('/', trim($this->getPathInfo(), '/'));
        }
        $this->setPathInfo($pathInfo);
        return $this;
    }

    /**
     * Get the original request with the original path (ignore rewrite)
     * 
     * @return Zend_Controller_Request_Http 
     */
    public function getOriginalRequest()
    {
        $request = new Zend_Controller_Request_Http();
        $request->setPathInfo($this->getOriginalPathInfo());
        return $request;
    }

    /**
     *
     * @return string 
     */
    public function getRequestString()
    {
        return $this->_requestString;
    }

    /**
     * Get the base path for the current request
     * 
     * @return string 
     */
    public function getBasePath()
    {
        $path = parent::getBasePath();
        if (empty($path)) {
            $path = '/';
        } else {
            $path = str_replace('\\', '/', $path);
        }
        return $path;
    }

    /**
     * Get the base url for the current request
     * 
     * @return string 
     */
    public function getBaseUrl()
    {
        $url = parent::getBaseUrl();
        $url = str_replace('\\', '/', $url);
        return $url;
    }

    /**
     *
     * @param string $route
     * @return Core_Controller_Request_Http 
     */
    public function setRouteName($route)
    {
        $this->_route = $route;
        $router = App_Main::getControllerFrontend()->getRouterByRoute($route);
        if (!$router) return $this;
        $module = $router->getFrontNameByRoute($route);
        if ($module) {
            $this->setModuleName($module);
        }
        return $this;
    }

    /**
     *
     * @return string 
     */
    public function getRouteName()
    {
        return $this->_route;
    }

    /**
     * Get HTTP HOST
     *
     * @param bool $trimPort
     * @return string
     */
    public function getHttpHost($trimPort = true)
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return false;
        }
        if ($trimPort) {
            $host = split(':', $_SERVER['HTTP_HOST']);
            return $host[0];
        }
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Set key=>value in the $_POST superglobal
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return Core_Controller_Request_Http
     */
    public function setPost($key, $value = null)
    {
        if (is_array($key)) {
            $_POST = $key;
        }
        else {
            $_POST[$key] = $value;
        }
        return $this;
    }

    /**
     * Specify module mached for the request
     *
     * @param string $module
     * @return Core_Controller_Request_Http
     */
    public function setControllerModule($module)
    {
        $this->_controllerModule = $module;
        return $this;
    }

    /**
     * Get module name of currently used controller
     *
     * @return string
     */
    public function getControllerModule()
    {
        return $this->_controllerModule;
    }

    /**
     * Get the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * Set the controller name
     * 
     * @param string 
     * @return Core_Controller_Request_Http
     */
    public function setControllerName($controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * Retrieve the controller name
     * 
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controller;
    }

    /**
     * Set the action name
     * 
     * @param string $action
     * @return Core_Controller_Request_Http
     */
    public function setActionName($action)
    {
        $this->_action = $action;
        return $this;
    }

    /**
     * Retrieve the action name
     * 
     * @return string
     */
    public function getActionName()
    {
        return $this->_action;
    }

    /**
     * Get route name used in request (ignore rewrite)
     * 
     * @return string
     */
    public function getRequestedRouteName()
    {
        if ($this->_requestedRouteName === null) {
            if ($this->_rewritedPathInfo !== null && isset($this->_rewritedPathInfo[0])) {
                $fronName = $this->_rewritedPathInfo[0];
                $router = App_Main::getControllerFrontend()->getRouterByFrontName($fronName);
                $this->_requestedRouteName = $router->getRouteByFrontName($fronName);
            } else {
                // no rewritten path found, use default route name
                return $this->getRouteName();
            }
        }
        return $this->_requestedRouteName;
    }

    /**
     * Get controller name used in request (ignore rewrite)
     * 
     * @return string
     */
    public function getRequestedControllerName()
    {
        if (($this->_rewritedPathInfo !== null) && isset($this->_rewritedPathInfo[1])) {
            return $this->_rewritedPathInfo[1];
        }
        return $this->getControllerName();
    }

    /**
     * Get action name used in request (ignore rewrite)
     * 
     * @return string
     */
    public function getRequestedActionName()
    {
        if (($this->_rewritedPathInfo !== null) && isset($this->_rewritedPathInfo[2])) {
            return $this->_rewritedPathInfo[2];
        }
        return $this->getActionName();
    }
}