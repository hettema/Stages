<?php
/**
 * class Admin_Model_Resource_User_Collection
 * 
 * @package Admin
 * @subpackage User
 * @category Resource-Collection-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */class Admin_Model_Resource_User_Collection extends Core_Model_Resource_Collection_Abstract
{
    protected $tbl_user = 'core_user';
    
    protected function _construct()
    {
        $this->_init($this->tbl_user, 'user_id');
    }

    /**
     * Get the admin users list
     *
     * @return Core_Model_Object 
     */
    public function getResultCollection()
    {
        $query = $this->_getQuery();
        $query->resetQuery(); //reset the query params set, if any
        $query->queryCondition("c_user.firstname ASC", 'ORDER');
        $query->queryColumn(array("c_user.*"));
        $query->queryTable($this->tbl_user ." AS c_user");
        
        $read = $this->_getResource()->_getReadAdapter();
        $count = $read->fetchOne($query->prepareCountQuery(), 'count');
        $results = $read->fetchAll($query->prepareQuery());

        $collection = array();
        if(!empty($results)) {
            foreach ($results as $result) {
                $user = App_Main::getModel('admin/user', $result);
                $collection[] = $user;
            }
        }

        $resultColection = new Core_Model_Object();
        $resultColection->setCollection($collection);
        $resultColection->setTotalCount($count);
        if($this->getFilterValue('page')) {
            $resultColection->setPage($this->getFilterValue('page'));
            $resultColection->setLimit($this->getFilterValue('limit'));
        }
        return $resultColection;
    }
}
