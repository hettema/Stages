<?php
/**
 * class Stages_Model__Resource_User
 * User object model to handle the user data and user actions
 * 
 * @package Stages
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Model_Resource_User extends Core_Model_Resource_Abstract
{
    protected $tbl_user = 'stages_user_entity';
    protected $tbl_project = 'stages_project_entity';
    protected $tbl_milestone = 'stages_milestone_entity';
    protected $tbl_user_tester = 'connect_prefinery_beta_tester';

    protected function  _construct()
    {
         $this->_init($this->tbl_user, 'user_id');
    }

    /**
     *
     * @param string $username
     * @return array 
     */
    public function checkExistingUsername($username)
    {
        return App_Main::getDbAdapter()->fetchRow("SELECT * FROM " . $this->tbl_user . " WHERE username = ". $this->_prepareValueForSave($username));
    }

    /**
     * called while login in with the user name, so the beta tester account is also verified here
     * @param <type> $username
     * @return <type>
     */
    public function loadByUsername($username)
    {
        $data = $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_user ." WHERE username=". $this->_prepareValueForSave($username));
        /*if(!empty ($data['user_id'])) {
            $data['is_beta_tester'] = (bool)$this->_getReadAdapter()->fetchOne("SELECT beta_user_id FROM ". $this->tbl_user_tester ." WHERE user_id=". $data['user_id'] ." AND is_active=1");
        }*/
        return $data;
    }

    /**
     * Reset the password with a new supplied/autogenerated password
     * 
     * @param int $userId
     * @param string $password
     * @param bool $changeInNextLogin
     * @return type 
     */
    public function resetPassword($userId, $password, $changeInNextLogin = false)
    {
        if($changeInNextLogin) {
            return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_user ." SET password=". $this->_prepareValueForSave($password) .", prompt_password_change = 1 WHERE user_id=". $userId );
        }
        return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_user ." SET password=". $this->_prepareValueForSave($password) .", prompt_password_change = 0 WHERE user_id=". $userId );

    }
    
    /**
     * Process the user object after user::load()
     * 
     * @param Core_Model_Abstract $object
     * @return Core_Model_Abstract 
     */
    protected function  _afterLoad(Core_Model_Abstract $object)
    {
        /*if($this->_getReadAdapter()->fetchOne("SELECT prefinery_id FROM ". $this->tbl_user_tester ." WHERE user_id=". $object->getId() ." AND is_active=1")) {
            $object->setIsBetaTester(true);
        }*/
        return $object;
    }

    /**
     * Grant tester acces to the user in the prefinery account
     * @todo function is disabled as the prefinery module is not included
     * 
     * @param Core_Model_Object $object
     * @param type $prefineryId 
     */
    public function grantTesterAccess(Core_Model_Object $object, $prefineryId = null)
    {
        /*if($object->getEmail()) {
            $exists = $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_user_tester ." WHERE email='". $object->getEmail() ."'");
        }
        if(empty($exists) && !empty($prefineryId)) {
            $exists  = $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_user_tester ." WHERE prefinery_id=".$prefineryId);
        }
        if(empty($exists)) {
            $prefineryId = empty ($prefineryId) ? $object->getId() : $prefineryId;
            return $this->_getWriteAdapter()->query("INSERT INTO ". $this->tbl_user_tester ." (prefinery_id, user_id, is_active, email, updated_at) VALUES (". $prefineryId .",". $object->getId() .", 1, '". $object->getEmail() ."', '". now() ."')");
        }
        return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_user_tester ." SET user_id=". $object->getId() .",is_active=1, updated_at='". now() ."' WHERE beta_user_id=". $exists['beta_user_id']);*/
    }
}

?>
