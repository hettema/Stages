<?php
/**
 * class Core_Controller_Action
 * 
 * @package Core
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Action
{
    protected $_request;
    protected $_response;
    protected $_session;
    protected $_website;
    protected $_realModuleName;
    protected $_flags = array();
   
    /**
     * Currently used area
     *
     * @var string
     */
    protected $_currentArea = 'frontend';
    
    protected $noaccess_action = "noaccess";

    protected $_disableIPFilter = false;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->_request = $request;
        $this->_response= $response;

        App_Main::getControllerFrontend()->setAction($this);

        $this->_construct();
    }

    protected function _construct()
    {

    }

    public function getRequest()
    {
        return $this->_request;
    }
    
    public function getRequestParam($key)
    {
        return $this->getRequest()->getParam($key);
    }

    public function getLayout()
    {
        return App_Main::getLayout();
    }
    
    public function getResponse()
    {
        return $this->_response;
    }

    protected function _getSession()
    {
        if(!$this->_session) {
            $this->_session = App_Main::getSession();
        }
        return $this->_session;
    }

    protected function _getWebsite()
    {
        if(!$this->_website) {
            $this->_website = App_Main::getWebsite();
        }
        return $this->_website;
    }

    protected function _preDispatch()
    {
        //set the area in the design model
        $design = App_Main::getDesign()->setArea($this->_currentArea);
        $website = $this->_getWebsite();


        $namespace = $this->getLayout()->getArea();
        App_Main::getSingleton('core/session', array('name' => $this->_currentArea))->start();
        //load the website based configs only on frontend
        if($this->_currentArea == 'frontend') {
            $website->loadFromRequestUrl();
        //switch the theme based on website's theme if configured
            if($website->getTheme() != $design->getTheme() && !$design->isThemeLocked()) {
            $design->setTheme($website->getTheme());
        }

            if($this->_getWebsite()->getConfig('session-log-visitors')) {
                App_Main::getSingleton('visitor/visitor')->initByRequest();
            }
        }

        //set the theme from request or session
        if($this->getRequest()->getParam('_theme') || $this->_getSession()->getCustomTheme()) {
            if($theme = $this->getRequest()->getParam('_theme')) {
                $this->_getSession()->setCustomTheme($theme);
            } else {
                $theme = $this->_getSession()->getCustomTheme();
            }
            $design->setTheme($theme)->lockTheme();
        }

        $this->loadLayout();
        //@#ToDO update layout for the current request
        $this->_updatelayout();

        //init translator
        if($this->_currentArea == 'frontend' && $this->_getWebsite()->getConfig('fontend_translation_enabled')) {
        App_Main::getTranslator()->init($this->_currentArea);
        }

        return $this;
    }
    
    public function dispatch($action)
    {
        $this->_preDispatch();
        
        $action = $this->applyIPFilter($action);
        $actionMethodName = $this->getActionMethodName($action);

        if (!is_callable(array($this, $actionMethodName))) {
            $actionMethodName = 'norouteAction';
        }
        //dispatch the action
        $this->$actionMethodName();
        
        return $this->postDispatch();
    }

    protected function postDispatch()
    {
        $this->_getSession()->setLastUrl(App_Main::getUrl('*/*/*', array('_current'=>true)));

        if($this->_currentArea == 'frontend') {
            if($this->_getWebsite()->getConfig('session-log-visitors')) {
                App_Main::getSingleton('visitor/visitor')->saveByRequest();
            }
        }
        return $this;
    }

    protected function applyIPFilter($action)
    {
        if ($this->_currentArea != 'frontend' || $this->_disableIPFilter) return $action;
        $countryFilterEnabled = $this->_getWebsite()->getConfig("country_filter_enabled");
        $actionList = explode(",", $this->_getWebsite()->getConfig("country_filter_restricted_url_list"));
        $restricted = false;
        if ($countryFilterEnabled && (in_array("*", $actionList) || in_array($this->_request->_route, $actionList))) {
            $location = $this->_getSession()->getRemoteLocationDetails();
            if (!$location) {
                $location = App_Main::getHelper('core/http')->getRemoteLocationDetails();
                if (!$location) { // cannot get location - could be due to some error communicating with the location server
                    App_Main::throwException("Could not contact location server.");
                }
                $this->_getSession()->setRemoteLocationDetails($location);
            }
            $allowList = explode(",", $this->_getWebsite()->getConfig("country_filter_allow_list"));
            $restricted = count($allowList) > 0 ? true : false;
            foreach ($allowList  as $country) {
                if ($location->getCountryCode() === $country) {
                    $restricted = false;
                    break;
                }
            }
            foreach (explode(",", $this->_getWebsite()->getConfig("country_filter_deny_list")) as $country) {
                if ($location->getCountryCode() === $country) {
                    $restricted = true;
                    break;
                }
            }
        }
        $ipFilterEnabled = $this->_getWebsite()->getConfig("ip_filter_enabled");
        if ($ipFilterEnabled) {
            $allowList = explode(",", $this->_getWebsite()->getConfig("ip_filter_allow_list"));
            $remoteIP = $this->getRequest()->getServer('REMOTE_ADDR');
            foreach ($allowList  as $ip) {
                if ($remoteIP == $ip) { // do wild card match instead
                    $restricted = false;
                    break;
                }
            }
            $denyList = explode(",", $this->_getWebsite()->getConfig("ip_filter_deny_list"));
            foreach ($denyList  as $ip) {
                if ($remoteIP == $ip) {
                    $restricted = true;
                    break;
                }
            }
        }
        if ($restricted) {
            $action = $this->noaccess_action;
        }
        return $action;
    }

    public function hasAction($action)
    {
        return is_callable(array($this, $this->getActionMethodName($action)));
    }

    public function getActionMethodName($action)
    {
        $method = $action.'Action';
        return $method;
    }

    /**
     * Retrieve full bane of current action current controller and
     * current module
     *
     * @param   string $delimiter
     * @return  string
     */
    public function getFullActionName($delimiter='_')
    {
        $_request = $this->getRequest();
        return $_request->getRequestedRouteName().$delimiter.
               $_request->getRequestedControllerName().$delimiter.
               $_request->getRequestedActionName();
    }

    public function loadLayout()
    {
        $actionName = $this->getFullActionName();
        $this->getLayout()->setArea($this->_currentArea);
        $this->getLayout()->loadLayoutConfig();
        return $this;
    }

    /**
     * function to update the layout config afted loading
     */
    protected function _updatelayout()
    {
        
    }

    public function renderLayout($output='')
    {
       
       if (''!==$output) {
            $this->getLayout()->addOutputBlock($output);
        }

        $this->getLayout()->setDirectOutput(false);

        $output = $this->getLayout()->getOutput();

        $this->getResponse()->appendBody($output);
        return $this;
    }

    /**
     * Set redirect url into response
     *
     * @param   string $url
     * @return  Core_Controller_Action
     */
    protected function _redirectUrl($url)
    {
        $this->getResponse()->setRedirect($url);
        return $this;
    }

    /**
     * Set redirect into responce
     *
     * @param   string $path
     * @param   array $arguments
     */
    protected function _redirect($path, $arguments=array())
    {
        $this->getResponse()->setRedirect(App_Main::getUrl($path, $arguments));
        return $this;
    }

    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     */
    protected function _redirectSuccess($defaultUrl)
    {
        $successUrl = $this->getRequest()->getParam(self::PARAM_NAME_SUCCESS_URL);
        if (empty($successUrl)) {
            $successUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($successUrl)) {
            $successUrl = App_Main::getBaseUrl();
        }
        $this->getResponse()->setRedirect($successUrl);
        return $this;
    }

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     */
    protected function _redirectError($defaultUrl)
    {
        $errorUrl = $this->getRequest()->getParam(self::PARAM_NAME_ERROR_URL);
        if (empty($errorUrl)) {
            $errorUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($errorUrl)) {
            $errorUrl = App_Main::getBaseUrl();
        }
        $this->getResponse()->setRedirect($errorUrl);
        return $this;
    }

    /**
     * Set referer url for redirect in responce
     *
     * @param   string $defaultUrl
     * @return  Core_Controller_Action
     */
    protected function _redirectReferer($defaultUrl=null)
    {

        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? App_Main::getBaseUrl() : $defaultUrl;
        }

        $this->getResponse()->setRedirect($refererUrl);
        return $this;
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            $refererUrl = App_Main::getHelper('core')->urlDecode($url);
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = App_Main::getHelper('core')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = App_Main::getBaseUrl();
        }
        return $refererUrl;
    }
}
?>
