<?php
/**
 * class Stages_Controller_BasecampController
 * 
 * @package Stages
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Controller_BasecampController extends Core_Controller_Action
{

    /**
     * Initial connect function to get the user details and also to check the user tocken
     */
    public function connectAction()
    {
        $this->_getSession()->unsSignup();
        
        $token = $this->getRequest()->getParam('token');
        $host = $this->getRequest()->getParam('host');
        
        //Load the user object with the bc-token
        $user = App_Main::getModel('stages/user')->load($token, 'bc_auth_token');
        //login and redirect to homepage if the user is already registered
        if($user->getId()) { 
            $user->setSessionUser($user);
            echo Zend_Json::encode(array('redirect'=>  App_Main::getUrl('')));
            return;
        }
        //Get the Basecamp connect object
        $bcConnect = new Connect_Basecamp($host, $token, 'X', 'xml');
        $userXml = $bcConnect->getMe();
        if(!empty($userXml['body']) && $userXml['status'] == '200 OK') {
            $userArray = App_Main::getHelper('stages')->XMLToArray($userXml['body']);
            $return = array('success'=>1,
                            'username'=>$userArray['user-name'],
                            'firstname'=>$userArray['first-name'],
                            'lastname'=>$userArray['last-name'],
                            'avatar'=>$userArray['avatar-url'],
                            'token'=>$userArray['token'],
                            );
            $signUp = App_Main::getModel('core/object');
            $signUp->setMode('bc_token_connect');
            $signUp->setToken($token);
            $signUp->setHost($host);
            $this->_getSession()->setSignup($signUp);
        } else {
            $return = array('success'=>0);
        }

        echo Zend_Json::encode($return);
    }
}
?>
