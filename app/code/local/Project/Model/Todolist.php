<?php
/**
 * class Project_Model_Todolist
 * 
 * @package Project
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Todolist extends Stages_Model_Abstract
{
    /**
     * interval for refreshing the project todo from Basecamp
     */
    const INTRL_TODO_REFRESH = 600;
    
    protected $_todos = null;

    protected function _construct()
    {
        $this->_init('project/todolist');
    }

    /**
     * Get todolist for a project created already in Basecamp
     * 
     * @param integer $projectId basecamp project id
     * @param bool $fromBc
     * @return array milestones Project_Model_Todolist
     */
    public function loadTodolistFromBc($entityId, $type='project', $fromBc = true)
    {
        switch ($type)
        {
            case 'project':
                
            break;
            case 'milestone':
            break;
        }
        $respXml = $this->getBcConnect()->getTodoListsForProject($entityId);
        if(empty ($respXml['body'])) { return array(); }

        $tArray = $this->_getHelper()->XMLToArray(simplexml_load_string($respXml['body']));
        $todolists = array();
        if(!empty($tArray['todo-list'])) {
            $tArray = (!empty($tArray['todo-list']['id'])) ? array($tArray['todo-list']) : $tArray['todo-list'];
            foreach ($tArray as $data) {
                if(empty($data['id'])) { continue; }
                $todolist = App_Main::getModel('project/todolist')->load($data['id'], 'bc_id');
                if(!$todolist->getId()) {
                    $todolist->setTitle($data['name']);
                    $todolist->setProjectId($data['project-id']);
                    $todolist->setMilestoneId($data['milestone-id']);
                    $todolist->setDescription($data['description']);
                    $todolist->setTodoCount($data['completed-count'] + $data['uncompleted-count']);
                    $todolist->setTodoCompleted($data['completed-count']);
                    $todolist->setTodoUncompleted($data['uncompleted-count']);
                    $todolist->setBcId($data['id']);
                    $todolist->setBcStatus(!(bool)$data['uncompleted-count']);
                    $todolist->setBcTodoLoadedAt('0000-00-00 00:00:00');
                    $todolist->setAddedDate(now());
                    $todolist->setUpdatedDate(now());
                    $todolist->save();
                } else if($todolist->getTodoCompleted() != $data['completed-count'] || $todolist->getTodoUnCompleted() != $data['uncompleted-count']) {
                    $todolist->setTodoCompleted($data['completed-count']);
                    $todolist->setTodoUnCompleted($data['uncompleted-count']);
                    $todolist->save();
                } else if($todolist->getTitle() != $data['name']) {
                    $todolist->setTitle($data['name']);
                    $todolist->save();
                } else if($todolist->getMilestoneId() != $data['milestone-id']) {
                    $todolist->setMilestoneId($data['milestone-id']);
                    $todolist->save();
                }
                $todolists[] = $todolist;
            }
        }
        return $todolists;
    }
    
    /**
     * Load the todos under the todolist from Basecamp or local database 
     * 
     * @param bool $fromBc
     * @return array todos 
     */
    public function loadTodos($fromBc = false)
    {
         if($fromBc || time() - strtotime($this->getBcTodoLoadedAt()) > self::INTRL_TODO_REFRESH && (!isset($this->_todos))) {
            $this->_todos = App_Main::getModel('project/todo')->loadTodosFromBc($this->getBcId());
            $this->getResource()->updateBcTodoLoadedAt($this);
        } else if(!$fromBc && !isset($this->_todos)) { 
            $this->_todos = $this->getResource()->getTodos($this);
        }
        
        $this->_todos = !empty ($todos) ? $todos : array();
        return $this->_todos;
    }

    /**
     * Load the milestone corresponding to the todolist
     * If the milestone is not found in the local database it will be loaded from 
     * Basecamp and saved to the database
     * 
     * @return type 
     */
    public function loadMilestone()
    {
        if(!$this->getMilestoneId()) { return false; }
        $milestone = App_Main::getModel('project/milestone')->load($this->getMilestoneId(), 'bc_id');
        if(!$milestone->getId()) { //load the milestone from Basecamp
            $milestone->loadFromBc($this->getMilestoneId());
        }
        $this->setMilestone();
        if(!$milestone->getId()) {
            $milestone->setTitle($data['title']);
            $milestone->setMilestoneDate($data['user']);
            $milestone->setMilestoneDate($data['date']);
            $milestone->setProjectId($projectId);
            $milestone->setAddedDate(now());
            $milestone->setUpdatedDate(now());
            $milestone->save();
        } else if($milestone->getTitle() != $data['title'] && $milestone->getBcId()) { //save the title is it is changed from BC
            App_Main::getSession()->addError('You can not change the title of an already registed milestone');
        }
        //write the logic to save the milestone informaiton to basecamp
    }

    /**
     * Save the todolist information in to Basecamp as well as local database
     * 
     * @return Project_Model_Todolist 
     */
    public function saveTodolist()
    {
        if($this->getBcId()) {
            $resp = $this->getBcConnect()->updateTodoList($this->getBcId(), $this->getTitle(), $this->getDescription(), $this->getMilestoneId());
            if($resp['status'] && !stristr($resp['status'], 'OK')) { return null; }
        } else {
            $resp = $this->getBcConnect()->createTodoListForProject($this->getProjectId(), $this->getTitle(), $this->getDescription(), $this->getMilestoneId(),true,true);

            if(empty($resp['id'])) { return null; }
            $this->setBcId($resp['id']);
            $this->setAddedDate(now());
            $this->setBcCreatedDate(now());
            $this->setBcStatus(2);
            $this->setTodoCount(0);
            $this->setTodoCount(0);
        }

        $this->setUpdatedDate(now());
        $this->save();
        return $this;
    }
    
    /**
     * Get the todolist data for formating in JSON
     * - title
     * - bc_id
     * 
     * @return array 
     */
    public function prepareDataForJson()
    {
        $data = array();
        $data['title'] = $this->getTitle();
        $data['bc_id'] =  $this->getBcId();
        return $data;
    }
}
?>