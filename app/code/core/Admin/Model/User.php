<?php
/**
 * class Admin_Model_User
 * 
 * @package Admin
 * @subpackage User
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Model_User extends Core_Model_Abstract
{
    const FORGOT_EMAIL_TEMPLATE    = 'admin/emails/forgot_email_template';
    const FORGOT_EMAIL_IDENTITY    = 'admin/emails/forgot_email_identity';
    const MIN_PASSWORD_LENGTH = 7;

    protected $_eventPrefix = 'admin_user';

    protected function _construct()
    {
        $this->_init('admin/user');
    }

    /**
     * Processing data before saving user info
     *
     * @return Admin_Model_User
     */
    protected function _beforeSave()
    {
		if($this->userExists()) {
			$this->_dataSaveAllowed = false;
			App_Main::getSession()->addError('Username or email already exists');
			return false;
		}
        return parent::_beforeSave();
    }
    
    /**
     * Delete the admin user
     * 
     * @return Admin_Model_User 
     */
    public function delete()
    {
        $this->_getResource()->delete($this);
        return $this;
    }

    /**
     * Add a new user
     * 
     * @return Admin_Model_User 
     */
    public function add()
    {
        $this->_getResource()->add($this);
        return $this;
    }

    /**
     * Check whether the user account already exists, check for email and username
     * 
     * @return type 
     */
    public function userExists()
    {
        $result = $this->_getResource()->userExists($this);
        return ( is_array($result) && count($result) > 0 ) ? true : false;
    }

    public function getCollection() {
        return App_Main::getResourceModel('admin/user_collection', $this->getResource());
    }

    /**
     * Send email with new user password
     *
     * @return Admin_Model_User
     */
    public function sendNewPasswordEmail()
    {

        App_Main::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                self::FORGOT_EMAIL_TEMPLATE,
                self::FORGOT_EMAIL_IDENTITY,
                $this->getEmail(),
                $this->getName(),
                array('user' => $this, 'password' => $this->getPlainPassword()));


        return $this;
    }

    /**
     * Get the user name 'firstname lastname'
     * 
     * @param string $separator
     * @return string 
     */
    public function getName($separator=' ')
    {
        return $this->getFirstname() . $separator . $this->getLastname();
    }

    /**
     * Get the user id
     * 
     * @return int 
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * Authenticate user name and password and save login record
     *
     * @param string $username
     * @param string $password
     * @return boolean
     * @throws Core_Exception
     */
    public function authenticate($username, $password)
    {
        $result = false;

        try {
            $this->loadByUsername($username);
            $sensitive = true;

            if ($sensitive && $this->getId() && App_Main::getHelper('core')->validateHash($password, $this->getPassword())) {
                if ($this->getIsActive() != '1') {
                    App_Main::throwException('This account is inactive.');
                }
                $result = true;
            }
        }
        catch (Core_Exception $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }

    /**
     * Login user
     *
     * @param   string $login
     * @param   string $password
     * @return  Admin_Model_User
     */
    public function login($username, $password)
    {
        if ($this->authenticate($username, $password)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }

    /**
     * Reload the user info from the database
     * 
     * @return Admin_Model_User 
     */
    public function reload()
    {
        $id = $this->getId();
        $this->setId(null);
        $this->load($id);
        return $this;
    }

    /**
     * Load user by username
     * 
     * @param string $username
     * @return Admin_Model_User 
     */
    public function loadByUsername($username)
    {
        $this->setData($this->getResource()->loadByUsername($username));
        return $this;
    }

    /**
     * Get the encripted password string 
     * 
     * @param string $pwd
     * @return string 
     */
    protected function _getEncodedPassword($pwd)
    {
        return App_Main::getHelper('core')->getHash($pwd, 2);
    }

}
