<?php
/**
 * class Core_Model_Url
 * Core Url object manipulates and generattes all the urls for the system
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 *
 * URL
 *
 * Properties:
 *
 * - request
 *
 * - relative_url: true, false
 * - type: 'link', 'skin', 'js', 'media'
 *
 * - secure: true, false
 *
 * - scheme: 'http', 'https'
 * - user: 'user'
 * - password: 'password'
 * - host: 'localhost'
 * - port: 80, 443
 * - base_path: '/dev/main/'
 * - base_script: 'index.php'
 *
 * - storeview_path: 'storeview/'
 * - route_path: 'module/controller/action/param1/value1/param2/value2'
 * - route_name: 'module'
 * - controller_name: 'controller'
 * - action_name: 'action'
 * - route_params: array('param1'=>'value1', 'param2'=>'value2')
 *
 * - query: (?)'param1=value1&param2=value2'
 * - query_array: array('param1'=>'value1', 'param2'=>'value2')
 * - fragment: (#)'fragment-anchor'
 *
 * URL structure:
 *
 * https://user:password@host:443/base_path/[base_script][storeview_path]route_name/controller_name/action_name/param1/value1?query_param=query_value#fragment
 */
class Core_Model_Url extends Core_Model_Object
{
    const DEFAULT_CONTROLLER_NAME   = 'index';
    const DEFAULT_ACTION_NAME       = 'index';

    static protected $_configDataCache;
    static protected $_encryptedSessionId;

    /**
     * Reserved Route parametr keys
     *
     * @var array
     */
    protected $_reservedRouteParams = array(
        '_type', '_secure', '_forced_secure', '_use_rewrite', '_nosid',
        '_absolute', '_current', '_direct', '_fragment', '_escape', '_query',
        '_store_to_url'
    );

    /**
     * Controller request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Use Session ID for generate URL
     *
     * @var bool
     */
    protected $_useSession;

    protected function _construct()
    {

    }

    /**
     * Initialize object data from retrieved url
     */
    public function parseUrl($url)
    {
        $data   = parse_url($url);
        $parts  = array(
            'scheme'=>'setScheme',
            'host'  =>'setHost',
            'port'  =>'setPort',
            'user'  =>'setUser',
            'pass'  =>'setPassword',
            'path'  =>'setPath',
            'query' =>'setQuery',
            'fragment'=>'setFragment');

        foreach ($parts as $component=>$method) {
            if (isset($data[$component])) {
                $this->$method($data[$component]);
            }
        }
        return $this;
    }
    
    /**
     * Set URL query parameters
     */
    public function setQuery($data)
    {
        if ($this->_getData('query') == $data) {
            return $this;
        }
        $this->unsetData('query_params');
        return $this->setData('query', $data);
    }

    public function getQuery($escape = false)
    {
        if (!$this->hasData('query')) {
            $query = '';
            if (is_array($this->getQueryParams())) {
                $query = http_build_query($this->getQueryParams(), '', $escape ? '&amp;' : '&');
            }
            $this->setData('query', $query);
        }
        return $this->_getData('query');
    }

    public function setQueryParams(array $data, $useCurrent = false)
    {
        $this->unsetData('query');
        if ($useCurrent) {
            $params = $this->_getData('query_params');
            foreach ($data as $param => $value) {
                $params[$param] = $value;
            }
            $this->setData('query_params', $params);
            return $this;
        }

        if ($this->_getData('query_params')==$data) {
            return $this;
        }
        return $this->setData('query_params', $data);
    }

    public function getQueryParams()
    {
        if (!$this->hasData('query_params')) {
            $params = array();
            if ($this->_getData('query')) {
                foreach (explode('&', $this->_getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->_getData('query_params');
    }

    public function setQueryParam($key, $data)
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        return $this->setData('query_params', $params);
    }

    public function getQueryParam($key)
    {
        if (!$this->hasData('query_params')) {
            $this->getQueryParams();
        }
        return $this->_getData('query_params', $key);
    }

    public function setFragment($data)
    {
        return $this->setData('fragment', $data);
    }

    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Zend request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = App_Main::getRequest();
        }
        return $this->_request;
    }

    public function getType()
    {
        if (!$this->hasData('type')) {
            $this->setData('type', 'link');
        }
        return $this->_getData('type');
    }

    /**
     * Get the base URL
     *
     * @param array $params
     * @return string
     */
    public function getBaseUrl($params = array())
    {
        if (isset($params['_type'])) {
            $this->setType($params['_type']);
        }
        if (isset($params['_secure'])) {
            $this->setSecure($params['_secure']);
        }
        return App_Main::getBaseUrl($this->getType(), $this->getSecure());
    }

    public function setRouteName($data)
    {
        if ($this->_getData('route_name')==$data) {
            return $this;
        }
        $this->unsetData('route_front_name')
            ->unsetData('controller_name')
            ->unsetData('action_name')
            ->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    /**
     * Set Controller Name
     * 
     * Reset action name and route path if changed
     */
    public function setControllerName($data)
    {
        if ($this->_getData('controller_name')==$data) {
            return $this;
        }
        $this->unsetData('action_name')->unsetData('secure');
        return $this->setData('controller_name', $data);
    }

    /**
     * Set Action name
     * 
     */
    public function setActionName($data)
    {
        if ($this->_getData('action_name') == $data) {
            return $this;
        }
        return $this->setData('action_name', $data)->unsetData('secure');
    }

    public function setRouteParam($key, $data)
    {
        $params = $this->_getData('route_params');
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        return $this->setData('route_params', $params);
    }

    public function getRouteParam($key)
    {
        return $this->_getData('route_params', $key);
    }

    /**
     * Build url by requested path and parameters
     *
     * @param   string $routePath
     * @param   array $routeParams
     * @return  string
     */
    public function getUrl($routePath=null, $routeParams=null)
    {
        $escapeQuery = false;

        /**
         * All system params should be unseted before we call getRouteUrl
         * this method has condition for ading default controller anr actions names
         * in case when we have params
         */
        if (isset($routeParams['_fragment'])) {
            $this->setFragment($routeParams['_fragment']);
            unset($routeParams['_fragment']);
        }

        if (isset($routeParams['_escape'])) {
            $escapeQuery = $routeParams['_escape'];
            unset($routeParams['_escape']);
        }

        $query = null;
        if (isset($routeParams['_query'])) {
            $query = $routeParams['_query'];
            unset($routeParams['_query']);
        }

        $noSid = null;
        if (isset($routeParams['_nosid'])) {
            $noSid = (bool)$routeParams['_nosid'];
            unset($routeParams['_nosid']);
        }
        $url = $this->getRouteUrl($routePath, $routeParams);
        /**
         * Apply query params, need call after getRouteUrl for rewrite _current values
         */
        if ($query !== null) {
            if (is_string($query)) {
                $this->setQuery($query);
            } elseif (is_array($query)) {
                $this->setQueryParams($query, !empty($routeParams['_current']));
            }
            if ($query === false) {
                $this->setQueryParams(array());
            }
        }

        if ($noSid !== true) {
            $this->_prepareSessionUrl($url);
        }

        if ($query = $this->getQuery($escapeQuery)) {
            $url .= '?'.$query;
        }

        if ($this->getFragment()) {
            $url .= '#'.$this->getFragment();
        }

        return $this->escape($url);
    }
    
    public function getRouteUrl($routePath=null, $routeParams=null)
    {
        $this->unsetData('route_params');

        if (isset($routeParams['_direct'])) {
            return $this->getBaseUrl().$routeParams['_direct'];
        }

        if (!is_null($routePath)) {
            
            $paths = explode('/', $routePath);
            $corePaths = array_splice($paths,0, 3);

            foreach($corePaths as $key=>$value) {
                /**
                 * Set Route, Controller, Action Parameters
                 * Also set the query parameters if passed
                 */
                if(empty ($value)) { continue; }
                switch ($key)
                {
                    case 0:
                        $value = $value == '*' ? $this->getRequest()->getRequestedRouteName() : $value;                        
                        $this->setRouteName($value);
                    break;
                    case 1:
                        $value = $value == '*' ? $controller = $this->getRequest()->getRequestedControllerName() : $value;                        
                        $this->setControllerName($value);
                    break;
                    case 2:
                        $value = $value == '*' ? $controller = $this->getRequest()->getRequestedActionName() : $value;                        
                        $this->setActionName($value);
                    break;                        
                }
                $corePaths[$key] = $value;
            }
            $routePath = implode('/', $corePaths) . '/';
            
            if (!empty($paths)) {
                $params = array();               $this->unsetData('route_params');
                while (!empty($paths)) {
                    $key = array_shift($paths);
                    if (!empty($paths)) {
                        $value = array_shift($paths);  
                        $params[$key] = $value;
                        $routePath .= $key .'/'. $value .'/';
                    }
                }
                $routeParams = is_array($routeParams) ? array() : $routeParams;
                $routeParams = array_merge($params, $routeParams);                
            }
        }
        if (is_array($routeParams)) {
            $this->setRouteParams($routeParams);
            
            foreach ($this->getRouteParams() as $key=>$value) {
                if (is_null($value) || false===$value || ''===$value || !is_scalar($value)) {
                    continue;
                }
                $routePath .= $key.'/'.$value.'/';
            }
            
        }
        if ($routePath != '' && substr($routePath, -1, 1) !== '/') { $routePath.= '/'; }
        
        return $this->getBaseUrl().$routePath;
    }
    
    public function setRouteParams(array $data, $unsetOldParams=true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_forced_secure'])) {
            $this->setSecure((bool)$data['_forced_secure']);
            $this->setSecureIsForced(true);
            unset($data['_forced_secure']);
        } else {
            if (isset($data['_secure'])) {
                $this->setSecure((bool)$data['_secure']);
                unset($data['_secure']);
            }
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        if (isset($data['_current'])) {
            foreach ($this->getRequest()->getUserParams() as $key=>$value) {
                if (array_key_exists($key, $data) || $this->getRouteParam($key)) { continue; }
                $data[$key] = $value;
            }
            foreach ($this->getRequest()->getQuery() as $key=>$value) {
                $this->setQueryParam($key, $value);
            }
            unset($data['_current']);
        }
        
        $this->setData('route_params', $data);
        
        return $this;
    }
    
    public function getUseSession()
    {
        if (is_null($this->_useSession)) {
            $this->_useSession = App_Main::getConfig('session-sessionkey-url');
        }
        return $this->_useSession;
    }

    /**
     * Add session id to URL if configured
     *
     * @param string $url
     * @return Core_Model_Url
     */
    protected function _prepareSessionUrl($url)
    {
        if (!$this->getUseSession()) { return $this; }
        $session = App_Main::getSession();
        if ($sessionId = $session->getSessionId()) {
            $this->setQueryParam($session->getSessionIdQueryParam(), $sessionId);
        }
        return $this;
    }

    /**
     * Escape (enclosure) URL string
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $value = str_replace('"', '%22', $value);
        $value = str_replace("'", '%27', $value);
        $value = str_replace('>', '%3E', $value);
        $value = str_replace('<', '%3C', $value);
        return $value;
    }

    /**
     * Build url by direct url and parameters
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function getDirectUrl($url, $params = array()) 
    {
        $params['_direct'] = $url;
        return $this->getUrl('', $params);
    }

}