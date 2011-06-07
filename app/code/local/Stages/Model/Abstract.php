<?php
/**
 * class Stages_Model_Abstract
 * Abstract class for the model objects defined inside stages module
 * 
 * @package Stages
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
abstract class Stages_Model_Abstract extends Core_Model_Abstract
{
    const BC_CONNECT_FORMAT = 'xml'; //'simplexml'
    
    /**
     * 
     * @var Connect_Basecamp 
     */
    protected $_bcConnect;

    /**
     *
     * @var Stages_Helper_Data 
     */
    protected $_helperStages;

    /**
     * Get the helper object instace
     * 
     * @return Stages_Helper_Data 
     */
    public function _getHelper()
    {
        if(!$this->_helperStages) {
            $this->_helperStages = App_Main::getHelper('stages');
        }
        return $this->_helperStages;
    }
    
    /**
     * Get the website object instance 
     *  
     * @return Core_Model_Website 
     */
    private function getWebsite()
    {
        return App_Main::getWebsite();
    }
    
    /**
     * Get the core email object
     * 
     * @return Core_Model_Email 
     */
    protected function _getMail() {
        $email = App_Main::getModel('core/email');
        $email->setType('html');
        $email->setFromEmail($this->getWebsite()->getConfig('mail-default-sender-email'));
        $email->setFromName($this->getWebsite()->getConfig('mail-default-sender-name'));
        $email->setReturnPath($this->getWebsite()->getConfig('mail-default-sender-email'));
        $email->setBcc($this->getWebsite()->getConfig('mail-default-bcc-email'));
        $email->setLocale($this->getWebsite()->getLocale());
        $email->setTheme($this->getWebsite()->getTheme());
        return $email;
    }

    /**
     * Get the Basecamp API connector object
     * 
     * @return Connect_Basecamp 
     */
    public function getBcConnect()
    {
        //check for user session
        if(!$user = App_Main::getSession()->getUser()) {
            App_Main::throwException('User session not found. Please login to connect');
            return;
        }
        //check for bc host and bc token in the user object
        if(!$user->getBcHost() || !$user->getBcAuthToken()) {
            App_Main::throwException('Unable to connect to BC::unable to find sufficient credentials');
            return ;
        }
        
        //load the bc connect api object if not loaded already
        if(!$this->_bcConnect) {
            $this->_bcConnect = new Connect_Basecamp($user->getBcHost(), $user->getBcAuthToken(), 'X', self::BC_CONNECT_FORMAT);
        }
        return $this->_bcConnect;
    }
}
?>
