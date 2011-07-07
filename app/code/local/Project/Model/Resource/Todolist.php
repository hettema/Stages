<?php
/**
 * class Project_Model_Resource_Todolist
 * 
 * @package Project
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Resource_Todolist extends Core_Model_Resource_Abstract
{
    protected $tbl_main = 'stages_todolist_entity';
    protected $tbl_todo = 'stages_todo_entity';

    protected function  _construct()
    {
         $this->_init($this->tbl_main, 'todolist_id');
    }
    
    /**
     * Get the todos for the todolist from database
     * 
     * @param Core_Model_Object $object
     * @return array Project_Model_Todo 
     */
    public function getTodos(Core_Model_Object $object)
    {
        if(!$object->getBcId()) { return array(); }
        $result = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_todo ." WHERE todolist_id=". $object->getBcId());
        if(!$result) { return array(); }

        $todos = array();
        foreach($result as $data) {
            $todos[] = App_Main::getModel('project/todo', $data);
        }
        return $todos;
    }
    
    /**
     * Update the todo loaded time entry
     * 
     * @param Core_Model_Object $object
     * @return Core_Model_Object 
     */
    public function updateBcTodoLoaded(Core_Model_Object $object)
    {
        $_toDelete = array();
        foreach($this->getTodos($object) as $prevTodo) {
            //check whether the milestone is existing in the newly loaded milestone array, if not mark it for delete
            if(!$object->getTodoById($prevTodo->getBcId())) {
                $_toDelete[] = $prevTodo->getId();
            }
        }
        if(!empty ($_toDelete)) {
            $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_todo ." WHERE todo_id IN (". implode(',', $_toDelete) .") AND todolist_id='". $object->getBcId() ."'");
        }
                
        $this->_getWriteAdapter()->query("UPDATE ". $this->getMainTable() ." SET bc_todo_loaded_at='". $object->getBcTodoLoadedAt() ."' WHERE todolist_id=". $object->getId());
        return $object;
    }
    
}

?>
