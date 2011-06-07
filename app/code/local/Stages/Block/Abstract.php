<?php
/**
 * class Stages_Block_Abstract
 * 
 * @package Stages
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Block_Abstract extends Core_Block_Template
{
    protected $_user;


    /** 
     * Get the session user or load the user bu the id passed the request query
     *
     * @return type 
     */
    public function getUser()
    {
        if($this->_user) {
            return $this->_user;
        }
        
        if(App_Main::getRequest()->getParam('user') && App_Main::hasSingleton('stages/user')) {
            $user = App_Main::getSingleton('stages/user');
        } else if(App_Main::hasSingleton('stages/user')) {
            $user = App_Main::getSingleton('stages/user');
        }
        if(!empty($user)) {
            $this->_user = $user;
            return $this->_user;
        }

        //else load the user with the fbid or from the session
        if($userId = App_Main::getRequest()->getParam('user')) {
            $user =  (App_Main::getSession()->getUser()->getId() == $userId) ? App_Main::getSession()->getUser() : App_Main::getSingleton('stages/user')->load($userId);
        } else if(App_Main::getSession()->getUser()) {
            $user = App_Main::getSession()->getUser();
        }

        if($user->getId()) {
            $this->_user = $user;
            return $this->_user;
        }
        return false;
    }

    /**
     * Check whether the current user loaded is the sesion user
     * 
     * @return type 
     */
    public function isUserIsSessionUser()
    {
        return App_Main::getSession()->getUser() && $this->getUser() ? App_Main::getSession()->getUser()->getId() == $this->getUser()->getId() : false;
    }
}

?>
