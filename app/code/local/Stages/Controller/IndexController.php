<?php
/**
 * class Stages_Controller_IndexController
 * 
 * @package Stages
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Controller_IndexController extends Core_Controller_Action
{

    /**
     * Check whether the user is already logged in
     * @return bool 
     */    
    private function isUserLoggedIn()
    {
        return (bool)$this->_getSession()->getUser();
    }

    /**
     * Index action of the application
     * Load the login page if no user session else list the projects
     *
     * @return type 
     */
    public function indexAction()
    {
        $this->getLayout()->getBlock('root')->addBodyClass('home');
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'cms/home.phtml'));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');
        $this->renderLayout();
        
        //if(!$this->isUserLoggedIn()) { return $this->loginAction(); }
        
        
        //return $this->_redirect('project/index/view');
    }

    /**
     * Init login acreen
     */
    public function loginAction()
    {
        //Unset the session user if logged in already
        $this->_getSession()->unsUser(); 

        $this->getLayout()->getBlock('root')->addBodyClass('home login-page');
        $contentMain = $this->getLayout()->createBlock('stages/login', 'content-main', array('template'=>'stages/login.phtml', 'header_message'=>'Login To Stages'));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');
        //add the login failed block
        $loginFailed = $this->getLayout()->createBlock('core/template', 'login-failed', array('template'=>'stages/login_failed.phtml'));
        $this->getLayout()->getBlock('content')->append($loginFailed, 'login-failed');

        $this->renderLayout();
    }

    /**
     * Unset the user from session and redirect to home page a.k.a login page
     * @return bool 
     */
    public function logoutAction()
    {
        $this->_getSession()->unsUser();
        return $this->_redirect('');
    }
}
?>
