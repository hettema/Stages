<?php
/**
 * class Project_Model_Resource_Project
 * 
 * @package Project
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Resource_Project extends Core_Model_Resource_Abstract
{
    protected $tbl_user = 'stages_project_entity';
    protected $tbl_project = 'stages_project_entity';
    protected $tbl_milestone = 'stages_milestone_entity';
    protected $tbl_todolist = 'stages_todolist_entity';
    protected $tbl_time_entry = 'stages_time_entity';

    protected function  _construct()
    {
         $this->_init($this->tbl_user, 'project_id');
    }

    /**
     * Get milestones for the project
     * 
     * @param Core_Model_Object $object
     * @return array Project_Model_Milestone 
     */
    public function getMilestones(Core_Model_Object $object)
    {
        if(!$object->getBcId()) { return array(); }
        $result = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_milestone ." WHERE project_id=". $object->getBcId());
        if(!$result) { return array(); }

        $milestones = array();
        foreach($result as $data) {
            $milestones[] = App_Main::getModel('project/milestone', $data);
        }
        return $milestones;
    }
    
    /**
     * Get todolists for the project
     * 
     * @param Core_Model_Object $object
     * @return array Project_Model_Todolist 
     */
    public function getTodolists(Core_Model_Object $object)
    {
        if(!$object->getBcId()) { return array(); }
        $result = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_todolist ." WHERE project_id=". $object->getBcId());
        if(!$result) { return array(); }

        $todolists = array();
        foreach($result as $data) {
            $todolists[] = App_Main::getModel('project/todolist', $data);
        }
        return $todolists;
    }
    
    /**
     * Get time-entries for the project
     * 
     * @param Core_Model_Object $object
     * @return array Project_Model_Time 
     */
    public function getTimeEntries(Core_Model_Object $object)
    {
        if(!$object->getBcId()) { return array(); }
        $result = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_time_entry ." WHERE project_id=". $object->getBcId());
        if(!$result) { return array(); }

        $todolists = array();
        foreach($result as $data) {
            $todolists[] = App_Main::getModel('project/time', $data);
        }
        return $todolists;
    }
    
    /**
     * Update milestone loaded date
     * 
     * @param Core_Model_Object $object
     * @return Core_Model_Object 
     */
    public function updateBcMilestoneLoaded(Core_Model_Object $object)
    {
        $_toDelete = array();
        foreach($this->getMilestones($object) as $prevMilestone) {
            //check whether the milestone is existing in the newly loaded milestone array, if not mark it for delete
            if(!$object->getMilestoneById($prevMilestone->getBcId())) {
                $_toDelete[] = $prevMilestone->getId();
            }
        }
        if(!empty ($_toDelete)) {
            $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_milestone ." WHERE milestone_id IN (". implode(',', $_toDelete) .") AND project_id='". $object->getBcId() ."'");
        }
        
        $this->_getWriteAdapter()->query("UPDATE ". $this->getMainTable() ." SET bc_milestone_loaded_at='". $object->getBcMilestoneLoadedAt() ."' WHERE project_id=". $object->getId());
        return $object;
    }
    
    /**
     * Update todolist loaded date
     * 
     * @param Core_Model_Object $object
     * @return Core_Model_Object 
     */
    public function updateBcTodolistLoaded(Core_Model_Object $object)
    {
        $_toDelete = array();
        foreach($this->getTodolists($object) as $prevTodolist) {
            //check whether the todolist is existing in the newly loaded todolist array, if not mark it for delete
            if(!$object->getTodolistById($prevTodolist->getBcId())) {
                $_toDelete[] = $prevTodolist->getId();
            }
        }
        if(!empty ($_toDelete)) {
            $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_todolist ." WHERE todolist_id IN (". implode(',', $_toDelete) .") AND project_id='". $object->getBcId() ."'");
        }
        
        $this->_getWriteAdapter()->query("UPDATE ". $this->getMainTable() ." SET bc_todolist_loaded_at='". $object->getBcTodolistLoadedAt() ."' WHERE project_id=". $object->getId());
        return $object;
    }
    
    /**
     * Set flag to reload all project related date from Basecamp
     * 
     * @param Core_Model_Object $object 
     */
    public function setBcFullReload(Core_Model_Object $object)
    {
        $dateToSet = date('Y-m-d h:i:s', time() - 86400);
        $object->setBcMilestoneLoadedAt($dateToSet);
        $object->setBcTodolistLoadedAt($dateToSet);
        $this->_getWriteAdapter()->query("UPDATE ". $this->getMainTable() ." SET bc_milestone_loaded_at='". $dateToSet ."', bc_todolist_loaded_at='". $dateToSet ."' WHERE project_id=". $object->getId());
        
    }
}
?>
