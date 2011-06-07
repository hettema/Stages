<?php
/**
 * class Admin_Controller_IndexController
 * 
 * @package Admin
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Controller_IndexController extends Admin_Controller_Action
{
    protected function _outTemplate($tplName, $data=array())
    {
        $block = $this->getLayout()->createBlock('backend/template')->setTemplate("$tplName.phtml");
        $messages = $this->getLayout()->createBlock('core/session_messages', 'session_messages');
        $block->append($messages, 'session_messages');
        
        foreach ($data as $index=>$value) {
            $block->assign($index, $value);
        }
        $this->getResponse()->setBody($block->toHtml());
    }
    
    public function indexAction()
    {
        $this->_redirect('backend/index');
    }
    
    /**
     * Login to the admin area 
     */
    public function loginAction()
    {

        if (App_Main::getAdminSession()->isLoggedIn()) {
            $this->_redirect('*');
            return;
        }
        $loginData = $this->getRequest()->getParam('login');
        $data = array();

        if( is_array($loginData) && array_key_exists('username', $loginData) ) {
            $data['username'] = $loginData['username'];
        } else {
            $data['username'] = null;
        }

        $this->_outTemplate('login', $data);
    }

    /**
     * Destruct the current user session
     */
    public function logoutAction()
    {
        $auth = App_Main::getSingleton('admin/session')->unsetAll();
        App_Main::getSingleton('core/session')->unsetAll();
        App_Main::getSingleton('core/session')->addSuccess('You successfully logged out.');
        $this->_redirect('*');
    }

    /**
     * Reset the password
     */
    public function forgotpasswordAction ()
    {
        $email = $this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();
        if (!empty($email) && !empty($params)) {
            $collection = App_Main::getResourceModel('admin/user_collection');

            $collection->addFieldToFilter('email', $email);
            $collection->load(false);

            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $user = App_Main::getModel('admin/user')->load($item->getId());
                    if ($user->getId()) {
                        $pass = substr(md5(uniqid(rand(), true)), 0, 7);
                        $user->setPassword($pass);
                        $user->save();
                        $user->setPlainPassword($pass);
                        $user->sendNewPasswordEmail();
                        App_Main::getSingleton('core/session')->addSuccess('A new password was sent to your email address. Please check your email and click Back to Login.');
                        $email = '';
                    }
                    break;
                }
            } else {
                App_Main::getSingleton('core/session')->addError('Can\'t find email address.');
            }
        } elseif (!empty($params)) {
            App_Main::getSingleton('core/session')->addError('Email address is empty.');
        }


        $data = array(
            'email' => $email
        );
        $this->_outTemplate('forgotpassword', $data);
    }

    /** 
     * Edit the user profile 
     */
    public function edit_profileAction()
    {
        $userId = App_Main::getSingleton('admin/session')->getUser()->getUserId();
        $user = App_Main::getModel('admin/user')->load($userId);
        $contentMain = $this->getLayout()->createBlock('core/template', 'user_edit', array('template' => 'admin/edit_user_profile.phtml','user'=>$user));
        $this->getLayout()->getBlock('content')->append($contentMain, 'user_edit');
        $this->renderLayout();
    }
    
    /**
     * Submit the user profile
     */
    public function submit_user_profileAction()
    {
        $request = $this->getRequest();
        $userId = $request->getParam('user_id');
        if(empty($userId)) { $this->_redirect(''); }
        
        $user = App_Main::getModel('admin/user');
        $user->setUserId($userId);
        $user->setFirstname($request->getParam('firstname'));
        $user->setLastname($request->getParam('lastname'));
        $user->setEmail($request->getParam('email'));
        if($request->getParam('change_password')&& $request->getParam('new_password') && $request->getParam('new_password') != 'new_password') {
            $user->setNewPassword($request->getParam('new_password'));
        App_Main::getSession()->addSuccess('User account password changed to '. $user->getNewPassword());
        }
        $user->save();
        $this->_redirect('admin/index/edit_profile');
        App_Main::getSession()->addSuccess('User account profile updated');
    }
}
?>
