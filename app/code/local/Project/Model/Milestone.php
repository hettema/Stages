<?php
/**
 * class Project_Model_Milestone
 * 
 * @package Project
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Milestone extends Stages_Model_Abstract
{
    protected $_todolists = array();
    protected $_todolistLoadedAt;


    protected function _construct()
    {
        $this->_init('project/milestone');
    }

    /**
     * Get milestones for a project created already in Basecamp
     * 
     * @param integer $projectId basecamp project id
     * @param bool $fromBc
     * @return array milestones Local_Model_Milestone
     */
    public function loadMilestonesFromBc($projectId, $fromBc = true)
    {
        $respXml = $this->getBcConnect()->getMilestonesForProject($projectId);
        if(empty ($respXml['body'])) { return array(); }

        $mArray = $this->_getHelper()->XMLToArray(simplexml_load_string($respXml['body']));
        $milestones = array();
        if(!empty($mArray['milestone'])) {
            $mArray = (!empty($mArray['milestone']['id'])) ? array($mArray['milestone']) : $mArray['milestone'];
            foreach ($mArray as $data) {
                if(empty($data['id'])) { continue; }
                $milestone = App_Main::getModel('project/milestone')->load($data['id'], 'bc_id');
                
                $milestone->setTitle($data['title']);
                $milestone->setUserResponsible($data['responsible-party-id']);
                $milestone->setMilestoneDate(date('Y-m-d H:i:s', strtotime($data['deadline'])));
                $milestone->setProjectId($projectId);
                $milestone->setBcId($data['id']);
                $milestone->setBcCreatedDate(date('Y-m-d H:i:s', strtotime($data['created-on'])));
                $milestone->setBcStatus((bool)$data['completed']);
                if(!$milestone->getAddedDate()) {
                    $milestone->setAddedDate(now());
                }
                if(!$milestone->getId() ||
                   $milestone->getOrigData('title') != $milestone->getTitle() ||
                   $milestone->getOrigData('status') != $milestone->getBcStatus() ||
                   $milestone->getOrigData('milestone_date') != $milestone->getMilestoneDate()) {
                    
                    //save milestone data
                    $milestone->setUpdatedDate(now());
                    $milestone->save();
                }
                $milestones[] = $milestone;
            }
        }
        return $milestones;
    }

    /**
     * Load the milestone data from Basecamp
     * @param type $bcId
     * @return type 
     */
    public function loadFromBc($bcId)
    {
        $respXml = $this->getBcConnect()->getMilestonesForProject($projectId);
        if(empty ($respXml['body'])) { return array(); }

        return false;
    }

    /**
     * Save the milestone submitted from the project edit form
     * 
     * called from Project_Controller_CreateController::save_milestoneAction
     * 
     * @param string $title Milestone title
     * @param string $date Milestone date
     * @param int $user Milestone user Basecamp id
     * @param string $type Milestone type M|D
     * @return Project_Model_Milestone 
     */
    public function saveMilestone($title, $date, $user, $type)
    {
        $saveMilestone = false;
        
        //set milestone data
        if($this->getTitle() != $title) {
            $this->setTitle($title);
            $saveMilestone = true;
        }
        if($this->getUserResponsible() != $user) {
            $this->setUserResponsible($user);
            $saveMilestone = true;
        }
        if($this->getType() != $type) {
            $this->setType($type);
            $saveMilestone = true;
        }
        if(strtotime($this->getMilestoneDate()) != strtotime($date)) {
            $this->setMilestoneDate($date);
            $saveMilestone = true;
        }
        if(!$saveMilestone) { return $this; }
        
        if($this->getBcId()) { //update the milestone data on Basecamp account
            $resp = $this->getBcConnect()->updateMilestone($this->getBcId(),$this->getTitle(),$this->getMilestoneDate(),'person',$this->getUserResponsible(),true);
            if($resp['status'] && !stristr($resp['status'], 'OK')) { return null; }
        } else { //create a new milestone in Basecamp under the current project
            $resp = $this->getBcConnect()->createMilestoneForProject($this->getProjectId(),$this->getTitle(),$this->getMilestoneDate(),'person',$this->getUserResponsible(),true);

            if(empty($resp['id'])) { return null; }
            $this->setBcId($resp['id']);        
            $this->setAddedDate(now());
            $this->setBcCreatedDate(now());
            $this->setBcStatus(2);
            
            //create todolist
            if($tlSaved = $this->createTodolist($this->getTitle())) {
                $todolists[] = $tlSaved;
            }
        }
        
        $this->setUpdatedDate(now());
        $this->save();
        return $this;
    }

    /**
     * Create todolist for the milestone
     * 
     * @param string $title
     * @return Project_Model_Todolist 
     */
    public function createTodolist($title)
    {
        $todolist = App_Main::getModel('project/todolist');
        $todolist->setTitle($title);
        $todolist->setProjectId($this->getProjectId());
        $todolist->setMilestoneId($this->getBcId());
        $todolist->setDescription('');
        return $todolist->saveTodolist();
    }
    
    /**
     * Prepare the milestone data for JSON
     * 
     * - type
     * - title
     * - ms_user
     * - ms_date
     * - bc_id
     * 
     * @return array 
     */
    public function prepareDataForJson()
    {
        $msData = array();
        $msData['type'] = $this->getType() ? $this->getType() : 'd';
        $msData['title'] = $this->getTitle();
        $msData['ms_user'] = $this->getUserResponsible();
        $msData['ms_date'] =  App_Main::getHelper('stages')->formatDateForJs(strtotime($this->getMilestoneDate()));
        $msData['bc_id'] =  $this->getBcId();
        return $msData;
    }
}
?>