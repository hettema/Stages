<?php
/**
 * class Admin_Controller_UserController
 * 
 * @package Admin
 * @subpackage User
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Controller_UserController extends Admin_Controller_Action
{
    private function updateLayout()
    {
        $tmplAction = $this->getLayout()->createBlock('backend/template', 'menu-content', array('template'=>'settings/content_header.phtml'));
        $this->getLayout()->getBlock('content')->append($tmplAction, 'menu-content');
        
        $topbar = $this->getLayout()->createBlock('backend/template', 'content-header', array('template'=>'admin/user/content_header.phtml'));
        $this->getLayout()->getBlock('content')->append($topbar, 'content-header');
    }
    
    public function indexAction()
    {
        $this->updateLayout();
        $this->getLayout()->getBlock('head')->setTitle('Admin Users', false, true); //add default sufifx but not prefix

        $tmplContent = $this->getLayout()->createBlock('admin/user_list', 'main-content', array('template'=>'admin/user/list.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($tmplContent, 'main-content');
        $this->renderLayout();
    }
    
    /**
     * List admin users
     */
    public function listAction()
    {
        return $this->indexAction(); //index action lists all users
    }
    
    /**
     * Edit selected admin user
     */
    public function editAction()
    {
        $this->updateLayout();
        $user = App_Main::getSingleton('admin/user');
        $user->load($this->getRequest()->getparam('id'));

        if(!$user || !$user->getId()) {
            $this->_redirect('page_notfound.html');
            return false;
        }
        
        $this->getLayout()->getBlock('head')->setTitle('Edit Admin User :: '. $user->getFirstname(), false);

        $contentMain = $this->getLayout()->createBlock('backend/template', 'user_edit', array('template'=>'admin/user/edit.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($contentMain, 'user_edit');
        $this->renderLayout();
    }

    /**
     * Add new admin user
     */
    public function addAction()
    {
        $this->updateLayout();
        $this->getLayout()->getBlock('head')->setTitle('Add New User', false);

        $contentMain = $this->getLayout()->createBlock('backend/template', 'user_edit', array('template'=>'admin/user/edit.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($contentMain, 'user_edit');
        $this->renderLayout();

    }

    /**
     * Submit the admin user info
     */
    public function submitAction()
    {
        $request = $this->getRequest();
        $user = App_Main::getModel('admin/user');
        $user->setFirstname($request->getParam('firstname'));
        $user->setLastname($request->getParam('lastname'));
        $user->setUsername($request->getParam('username'));
        if(!$request->getParam('user_id')) {
            $user->setCreated(now());
            $user->setPassword($request->getParam('password'));
        } else {
            $user->setUserId($request->getParam('user_id'));
            if($request->getParam('password') && $request->getParam('password') != ''  && $request->getParam('change_password')) {
                $user->setPassword($request->getParam('password'));
            }
        }
        $user->setEmail($request->getParam('email'));
        $user->setModified(now());
        $user->setRoleParentId($request->getParam('user_role'));
        
        $user->save();
        $this->_redirect('admin/user/list');
    }

    /**
     * Delete selected admin user
     */
    public function deleteAction()
    {
        $request = $this->getRequest();
        if(!$request->getParam('id')) { return false; }

        $user = App_Main::getModel('admin/user', array('user_id'=>$request->getParam('id')));
        $user->delete();
        $this->_redirect('admin/user');
    }
}
?>