<?php
/**
 * class Project_Model_Time
 * 
 * @package Project
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Time extends Stages_Model_Abstract
{
    protected $_todolists = array();


    protected function _construct()
    {
        $this->_init('project/time');
    }

    /**
     * Get time-entries for a project created already in Basecamp
     * Save the time entry info if it is not loaded into database
     * - description
     * - project_id
     * - todo_id
     * - person_id
     * - bc_date
     * - hours
       - bc_id
     * 
     * @param integer $projectId basecamp project id
     * @param bool $fromBc
     * @return array milestones Project_Model_Time
     */
    public function loadTimeFromBc($entityId, $type='project', $page = 0, $fromBc = true)
    {
        switch ($type)
        {
            case 'project': //load time for project
                $respXml = $this->getBcConnect()->getTimeEntriesForProject($entityId, $page);
            break;
            case 'todo': //load time for todo
                $respXml = $this->getBcConnect()->getTimeEntriesForTodoItem($entityId, $page);
            break;
        }
        
        if(empty ($respXml['body'])) { return array(); }
        if($respXml['pages'] < $page) { return array(); }
        
        //parse the response body into array
        $tArray = $this->_getHelper()->XMLToArray(simplexml_load_string($respXml['body']));
        $timeEntries = array();
        if(!empty($tArray['time-entry'])) {
            $tArray = (!empty($tArray['time-entry']['id'])) ? array($tArray['time-entry']) : $tArray['time-entry'];
            foreach ($tArray as $data) {
                if(empty($data['id'])) { continue; }
                $timeEntry = App_Main::getModel('project/time')->load($data['id'], 'bc_id');
                
                $timeEntry->setDescription(substr($data['description'], 0, 254));
                $timeEntry->setProjectId($data['project-id']);
                $timeEntry->setTodoId($data['todo-item-id']);
                $timeEntry->setPersonId($data['person-id']);
                $timeEntry->setBcDate($data['date']);
                $timeEntry->setHours($data['hours']);
                $timeEntry->setBcId($data['id']);
                if(!$timeEntry->getAddedDate()) {
                    $timeEntry->setAddedDate(now());
                }
                
                if(!$timeEntry->getId() ||
                   $timeEntry->getOrigData('todo_id') != $timeEntry->getTodoId() ||
                   $timeEntry->getOrigData('person_id') != $timeEntry->getPersonId() ||
                   $timeEntry->getOrigData('bc_date') != $timeEntry->getBcDate() ||
                   $timeEntry->getOrigData('hours') != $timeEntry->$timeEntry->getHours()) {
                    
                    //save time entry data
                    $timeEntry->setUpdatedDate(now());
                    $timeEntry->save();
                } 
                $timeEntries[] = $timeEntry;
            }
        }
        return $timeEntries;
    }
}
?>