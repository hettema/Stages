<?php
/**
 * class Admin_Model_Session
 * 
 * @package Admin
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Model_Session extends Core_Model_Session_Abstract
{
    const ADMIN_SESSION_NAMESPACE = 'admin';
    const ADMIN_SESSION_NAME = 'backend';

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->init(self::ADMIN_SESSION_NAMESPACE, self::ADMIN_SESSION_NAME);
    }

    /**
     * Try to login user in admin
     *
     * @param  string $username
     * @param  string $password
     * @param  Core_Controller_Request_Http $request
     * @return Admin_Model_User|null
     */
    public function login($username, $password, $request = null)
    {
        if (empty($username) || empty($password)) {
            return;
        }

        try {
            /* @var $user Admin_Model_User */
            $user = App_Main::getModel('admin/user');
            $user->login($username, $password);
            if ($user->getId()) {

                $session = App_Main::getSingleton('admin/session');
                $session->setIsFirstVisit(true);
                $session->setUser($user);
            } else {
                throw  App_Main::exception('Core', 'Invalid Username or Password.');
            }
        }
        catch (Core_Exception $e) {
            if ($request && !$request->getParam('messageSent')) {
                App_Main::getSingleton('core/session')->addError($e->getMessage());
                $request->setParam('messageSent', true);
            }
        }
        return $user;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getUser() && $this->getUser()->getId();
    }

    /**
     * Custom REQUEST_URI logic
     *
     * @param Core_Controller_Request_Http $request
     * @return string|null
     */
    protected function _getRequestUri($request = null)
    {
        return $request->getRequestUri();
    }
}
