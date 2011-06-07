<?php
/**
 * class Admin_Model_Resource_User
 * 
 * @package Admin
 * @subpackage User
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Admin_Model_Resource_User extends Core_Model_Resource_Abstract
{
    protected $tbl_user = 'core_user';
    
    protected function _construct()
    {
        $this->_init($this->tbl_user, 'user_id');
    }
	
    /**
     * Record the user last login informartion
     * 
     * @param Admin_Model_User $user
     * @return type 
     */
    public function recordLogin(Admin_Model_User $user)
    {        
        return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_user ." SET logdate='". now() ."', lognum=lognum+1 WHERE user_id='". $user->getUserId() ."'");
    }

    /**
     * Load the user data using the username
     * @param type $username
     * @return type 
     */
    public function loadByUsername($username)
    {
        return $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_user ." WHERE username='". $username ."'");
    }

    /**
     * Delete the user from the user table
     * 
     * @param Core_Model_Abstract $user
     * @return type 
     */
    public function delete(Core_Model_Abstract $user)
    {        
        $uid = $user->getId();
        if(!empty ($uid)) {
            return $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_user ." WHERE user_id=". $uid ." LIMIT 1");
        }
        return false;
    }

    /**
     * Check whether the user already exists
     * checks for email  and username
     * 
     * @param Core_Model_Abstract $user
     * @return type 
     */
    public function userExists(Core_Model_Abstract $user)
    {
        return $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_user ." WHERE (username = '". $user->getUsername() ."' OR email = '". $user->getEmail() ."') AND user_id != '". $user->getId() ."'");
    }
}
?>
