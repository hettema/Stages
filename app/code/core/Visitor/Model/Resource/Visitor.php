<?php
/**
 * class Visitor_Model_Resource_Visitor
 * 
 * @package Visitor
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Visitor_Model_Resource_Visitor extends Core_Model_Resource_Abstract
{
    protected $tbl_visitor = 'log_visitor';
    protected $tbl_visitor_info = 'log_visitor_info';
    protected $tbl_url = 'log_url';
    protected $tbl_url_info = 'log_url_info';
    
    protected function _construct()
    {
        $this->_init($this->tbl_visitor, 'visitor_id');
    }

    /**
     *
     * @param Core_Model_Visitor $visitor
     * @return array
     */
    protected function _prepareDataForSave(Core_Model_Abstract $visitor, $graceful = false, $table = false)
    {
        return array(
            'session_id'    => $this->_prepareValueForSave($visitor->getSessionId()),
            'first_visit_at'=> $this->_prepareValueForSave($visitor->getFirstVisitAt()),
            'last_visit_at' => $this->_prepareValueForSave($visitor->getLastVisitAt()),
            'last_url_id'   => $visitor->getLastUrlId() ? $visitor->getLastUrlId() : 0,
            'visitor_id'    => $visitor->getId() ? $visitor->getId() : 0,
            'website_id'    => $visitor->getWebsiteId() ? $visitor->getWebsiteId() : 0,
        );
    }

    protected function _beforeSave(Core_Model_Abstract $visitor)
    {
        if (!$visitor->getIsNewVisitor()) {
            $this->_saveUrlInfo($visitor);
        }
        return $this;
    }

    protected function _afterSave(Core_Model_Abstract $visitor)
    {
        if ($visitor->getIsNewVisitor()) {
            $this->_saveVisitorInfo($visitor);
            $visitor->setIsNewVisitor(false);
        }
        else {
            $this->_saveVisitorUrl($visitor);            
        }
        return $this;
    }

    /**
     * Save visitor information
     *
     * @param   Core_Model_Visitor $visitor
     * @return  Core_Model_Resource_Visitor
     */
    protected function _saveVisitorInfo(Core_Model_Abstract $visitor)
    {
        /* @var $stringHelper Core_Helper_String */
        $stringHelper = App_Main::getHelper('core/string');

        $referer    = $stringHelper->cleanString($visitor->getHttpReferer());
        $referer    = $stringHelper->substr($referer, 0, 255);
        $userAgent  = $stringHelper->cleanString($visitor->getHttpUserAgent());
        $userAgent  = $stringHelper->substr($userAgent, 0, 255);
        $charset    = $stringHelper->cleanString($visitor->getHttpAcceptCharset());
        $charset    = $stringHelper->substr($charset, 0, 255);
        $language   = $stringHelper->cleanString($visitor->getHttpAcceptLanguage());
        $language   = $stringHelper->substr($language, 0, 255);

        $write = $this->_getWriteAdapter();
        $data = array(
            'visitor_id'            => $visitor->getId(),
            'http_referer'          => $this->_prepareValueForSave($stringHelper->substr($visitor->getHttpReferer(), 0, 255)),
            'http_user_agent'       => $this->_prepareValueForSave($stringHelper->substr($visitor->getHttpUserAgent(), 0, 255)),
            'http_accept_charset'   => $this->_prepareValueForSave($stringHelper->substr($visitor->getHttpAcceptCharset(), 0, 255)),
            'http_accept_language'  => $this->_prepareValueForSave($stringHelper->substr($visitor->getHttpAcceptLanguage(), 0, 255)),
            'server_addr'           => $this->_prepareValueForSave($visitor->getServerAddr()),
            'remote_addr'           => $this->_prepareValueForSave($visitor->getRemoteAddr()),
        );

        $write->insert($this->tbl_visitor_info, $data);
        return $this;
    }

    /**
     * Save visitor and url relation
     *
     * @param   Core_Model_Visitor $visitor
     * @return  Core_Model_Resource_Visitor
     */
    protected function _saveVisitorUrl(Core_Model_Abstract $visitor)
    {
        $write = $this->_getWriteAdapter();
        $write->insert($this->tbl_url, array(
            'url_id'    => $visitor->getLastUrlId(),
            'visitor_id'=> $visitor->getId(),
            'visit_time'=> $this->_prepareValueForSave(now()),
        ));
        return $this;
    }

    /**
     * Save url information
     *
     * @param   Core_Model_Visitor $visitor
     * @return  Core_Model_Resource_Visitor
     */
    protected function _saveUrlInfo(Core_Model_Abstract $visitor)
    {
        $this->_getWriteAdapter()->insert($this->tbl_url_info, array(
            'url'    => $this->_prepareValueForSave(App_Main::getHelper('core/string')->substr($visitor->getUrl(), 0, 250)),
            'referer'=> $this->_prepareValueForSave(App_Main::getHelper('core/string')->substr($visitor->getHttpReferer(), 0, 250))
        ));
        $visitor->setLastUrlId($this->_getWriteAdapter()->lastInsertId());
        return $this;
    }
}
?>