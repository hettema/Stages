<?php
/**
 * class Project_Model_Todo
 * 
 * @package Project
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Todo extends Stages_Model_Abstract
{
    protected $_todolists = array();


    protected function _construct()
    {
        $this->_init('project/todo');
    }

    /**
     * Get todos for a project created already in Basecamp
     * 
     * @param integer $todolistId basecamp project id
     * @param bool $fromBc
     * @return array milestones Project_Model_Todo
     */
    public function loadTodosFromBc($todolistId, $projectId = null)
    {
        $respXml = $this->getBcConnect()->getTodoItemsForList($todolistId);
        if(empty ($respXml['body'])) { return array(); }

        $tArray = $this->_getHelper()->XMLToArray(simplexml_load_string($respXml['body']));
        $todos = array();
        if(!empty($tArray['todo-item'])) {
            $tArray = (!empty($tArray['todo-item']['id'])) ? array($tArray['todo-item']) : $tArray['todo-item'];
            foreach ($tArray as $data) {
                if(empty($data['id'])) { continue; }
                $todo = App_Main::getModel('project/todo')->load($data['id'], 'bc_id');
                $status = !(bool)$data['completed'];
                if(!$todo->getId()) {
                    $todo->setTitle(substr($data['content'],0, 254));
                    $todo->setTodolistId($data['todo-list-id']);
                    $todo->setBcId($data['id']);
                    $todo->setBcStatus($status);
                    $todo->setBcAddedDate(date('Y-m-d H:i:s', strtotime($data['created-on'])));
                    $todo->setBcUpdatedDate(date('Y-m-d H:i:s', strtotime($data['updated-at'])));
                    $todo->setCommentCount($data['comments-count']);
                    $todo->setAddedDate(now());
                    $todo->setUpdatedDate(now());
                    $todo->save();
                } else if($todo->getTitle() != $data['content']) {
                    $todo->setTitle($data['content']);
                    //$todo->save();
                } else if($todo->getTodolistId() != $data['todo-list-id']) {
                    $todo->setTodolistId($data['todo-list-id']);
                    $todo->save();
                }
                $todos[] = $todo;
            }
        }
        return $todos;
    }

    /**
     * Load the time registered for the todo from Basecamp
     * @todo implement the functionality
     */
    public function loadTime()
    {
        
    }
}
?>