<?php
/**
 * class Visitor_Model_Visitor
 * 
 * @package Visitor
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Visitor_Model_Visitor extends Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('visitor/visitor');
    }

    /**
     * get the session object
     *
     * @return Core_Model_Session
     */
    protected function _getSession()
    {
        return App_Main::getSingleton('core/session');
    }

    /**
     * Initialize visitor information from server data
     *
     * @return Core_Model_Visitor
     */
    public function initServerData()
    {
        /* @var $helper Core_Helper_Http */
        $helper = App_Main::getHelper('core/http');

        $this->addData(array(
            'server_addr'           => $helper->getServerAddr(true),
            'remote_addr'           => $helper->getRemoteAddr(true),
            'http_secure'           => false,
            'http_host'             => $helper->getHttpHost(true),
            'http_user_agent'       => $helper->getHttpUserAgent(true),
            'http_accept_language'  => $helper->getHttpAcceptLanguage(true),
            'http_accept_charset'   => $helper->getHttpAcceptCharset(true),
            'request_uri'           => $helper->getRequestUri(true),
            'session_id'            => $this->_getSession()->getSessionId(),
            'http_referer'          => $helper->getHttpReferer(true),
        ));

        return $this;
    }

    /**
     * Retrieve url from object data
     *
     * @return string url
     */
    public function getUrl()
    {
        $url = 'http' . ($this->getHttpSecure() ? 's' : '') . '://';
        $url .= $this->getHttpHost().$this->getRequestUri();
        return $url;
    }

    /**
     * Get/Set the first visit time
     * 
     * @return Visitor_Model_Visitor 
     */
    public function getFirstVisitAt()
    {
        if (!$this->hasData('first_visit_at')) {
            $this->setData('first_visit_at', now());
        }
        return $this->getData('first_visit_at');
    }

    /**
     * Get/Set the last visit time
     * 
     * @return Visitor_Model_Visitor 
     */
    public function getLastVisitAt()
    {
        if (!$this->hasData('last_visit_at')) {
            $this->setData('last_visit_at', now());
        }
        return $this->getData('last_visit_at');
    }

    /**
     * Initialization visitor data from request
     *
     * Used in event "controller_action_predispatch"
     *
     * @return  Core_Model_Visitor
     */
    public function initByRequest()
    {
        $this->setData($this->_getSession()->getVisitorData());
        $this->initServerData();

        if (!$this->getId()) {
            $this->setFirstVisitAt(now());
            $this->setIsNewVisitor(true);
            $this->setWebsiteId(App_Main::getWebsite()->getId());
            $this->save();
        }
        return $this;
    }

    /**
     * Saving visitor information from the request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @return  Core_Model_Visitor
     */
    public function saveByRequest()
    {
        $this->setLastVisitAt(now());
        $this->save();

        $this->_getSession()->setVisitorData($this->getData());
        return $this;
    }    
}