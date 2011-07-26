<?php
/**
 * class Project_Model_Project
 * 
 * @package Project
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Project extends Stages_Model_Abstract
{
    /**
     * interval for refreshing the project milestone from Basecamp
     */
    const INTRL_MILESTONE_REFRESH = 600;
    /**
     * interval for refreshing the project todolist from Basecamp
     */
    const INTRL_TODOLIST_REFRESH = 300;
    
    /**
     * interval for refreshing the project todo from Basecamp
     */
    const INTRL_TODO_REFRESH = 300;
    
    /**
     * interval for refreshing the project time entries from Basecamp
     */
    const INTRL_TIMEENTRY_REFRESH = 1200;
    
    protected $_milestones = null;
    protected $_todolists = null;
    protected $_todos = array();
    protected $_timeEntries = null;
    protected $_milestoneLoadedAt;
    protected $_todolistLoadedAt;
    protected $_todoLoadedAt;
    protected $_timeEntryLoadedAt;


    protected function _construct()
    {
        $this->_init('project/project');
    }
    
    /**
     * Load all the projects from Basecamp ans save it to the local database
     * 
     * @return type 
     */
    public function loadProjectsFromBc()
    {
        $respXml = $this->getBcConnect()->getProjects();
        if(empty ($respXml['body'])) { return false; }

        //get the helper object Stages_Helper_Data
        $_helper = $this->_getHelper();
        $_respXml = simplexml_load_string($respXml['body']);

        $projects = array();
        foreach($_respXml->project as $_xml) {
            //parse the project xml into array
            $data = $_helper->XMLToArray($_xml);
            $project = $this->loadProjectFromBcData($data);            
            if(empty ($project)) { continue; }
            
            $projects[] = $project;
        }
        return $projects;
    }
    
    /**
     * Load the project data array into project object model instance
     * 
     * @param array $data
     * @return Project_Model_Project 
     */
    public function loadProjectFromBcData($data)
    {
        if(empty($data['id'])) { return false; }
        $project = App_Main::getModel('project/project')->load($data['id'], 'bc_id');

        //calculate the start date and end date for a new project
        $createdTs = strtotime($data['created-on']);
        $lastUpdatedTs = strtotime($data['last-changed-on']);
        if(!$project->getStartDate()) {
            $project->setStartDate($this->calculateStartDate($createdTs));
        }
        $createdTs = strtotime($project->getStartDate());
        //set the end date to the last updated date
        $endDateTs = ($lastUpdatedTs - $createdTs) / 86400 < 27 ? (27 * 86400) + $createdTs : $lastUpdatedTs;
        $endDate = $this->calculateEndDate($endDateTs);            
        $project->setEndDate($endDate);            
        $project->setTitle($data['name']);
        $project->setBcId($data['id']);
        $project->setBcStatus($data['status']);
        $project->setBcCreatedDate(date('Y-m-d H:i:s', strtotime($data['created-on'])));            
        if(!$project->getAddedDate()) {
            $project->setAddedDate(now());
        }
        if(!$project->getId() || 
            $project->getOrigData('title') != $project->getTitle() || //update the name of the project if it is updated in Basecamp
            $project->getOrigData('end_date') != $project->getEndDate()) { //update the end date attribute of the project if it is updated at a later time compared to the current one Basecamp       

            // save the project data
            $project->setUpdatedDate(now());
            $project->save();
        } 
        return $project;
    }

    /**
     * Get the milestones for the project from Basecamp or local database
     * (if flag $fromBC is set or milestone load interval > INTRL_MILESTONE_REFRESH)
     * 
     *
     * @param bool $fromBc
     * @param bool $loadFromDb
     * @return array milestones 
     */
    public function getMilestones($fromBc = false, $fromLocal = false)
    {
        if($fromLocal) { //get the cached milestones from DB 
            return $this->getResource()->getMilestones($this);
        }
        
        if($fromBc || time() - strtotime($this->getBcMilestoneLoadedAt()) > self::INTRL_MILESTONE_REFRESH) {
            $this->_milestones = App_Main::getModel('project/milestone')->loadMilestonesFromBc($this->getBcId());
            
            $this->setBcMilestoneLoadedAt(now());
            $this->getResource()->updateBcMilestoneLoaded($this);
        } else if(!isset($this->_milestones)) { 
            $this->_milestones = $this->getResource()->getMilestones($this);
        }
        
        return !empty($this->_milestones) ? $this->_milestones : array();
    }

    /**
     * Get the todolist for the project from Basecamp or local database
     * (if flag $fromBC is set or todolist load interval > INTRL_TODOLIST_REFRESH)
     * 
     *
     * @param bool $fromBc
     * @param bool $loadFromDb
     * @return array todolists 
     */
    public function getTodoLists($fromBc = false, $fromLocal = false)
    {
        if($fromLocal) { 
            //get the cached todolists from DB 
            return $this->getResource()->getTodolists($this);
        }
        if($fromBc || time() - strtotime($this->getBcTodolistLoadedAt()) > self::INTRL_TODOLIST_REFRESH) {
            $this->_todolists = App_Main::getModel('project/todolist')->loadTodolistFromBc($this->getBcId());
            
            $this->setBcTodolistLoadedAt(now());
            $this->getResource()->updateBcTodolistLoaded($this);
        } else if(!isset($this->_todolists)) { 
            $this->_todolists = $this->getResource()->getTodolists($this);
        }
        
        return $this->_todolists;        
    }

    /**
     * Get the todo for the project from Basecamp or local database
     * (if flag $fromBC is set or todo load interval > INTRL_TODO_REFRESH)
     * 
     *
     * @param bool $fromBc
     * @param bool $loadFromDb
     * @return type 
     */
    public function getTodos($fromBc = false)
    {
        //if(is_array($this->_todos) || time() - $this->_todoLoadedAt < self::INTRL_TODO_REFRESH) { return $this->_todos; }
        $this->_todos = array();
        foreach($this->getTodoLists() as $todolist) {
            $todos = $todolist->loadTodos($fromBc);
            if(empty ($todos)) { continue; }
            
            $this->_todos = array_merge($this->_todos, $todos);
        }
        return $this->_todos;
    }
    
    /**
     * Get the tme-entries for the project from Basecamp or local database
     * (if flag $fromBC is set or time-entries load interval > INTRL_TIMEENTRY_REFRESH)
     * 
     *
     * @param bool $fromBc
     * @param bool $loadFromDb
     * @return type 
     */
    public function getTimeEntries($fromBc = false, $reloadAll = false)
    {
        $fromBc = ($fromBc || time() - $this->_timeEntryLoadedAt < self::INTRL_TIMEENTRY_REFRESH);
        if(isset($this->_timeEntries) && !$fromBc) { return $this->_timeEntries; }
        
        //load the cached time entry data from DB
        $this->_timeEntries = $this->getResource()->getTimeEntries($this);
        if($fromBc) { 
            $this->_timeEntryLoadedAt = time();
            $page = 0;
            if(!isset ($this->_timeEntries) || $reloadAll) { 
                //unset the data from DB if a full refresh from BC is requested
                $this->_timeEntries  = array();
            } else if(!$reloadAll) {
                //load from the last pages
                $page = ceil(count($this->_timeEntries) /50) - 1; 
            }
            
            //load the time-entries iterating over all the pages
            while($page >= 0) {
                $results =  App_Main::getModel('project/time')->loadTimeFromBc($this->getBcId(), 'project', $page);
                //stop iteration (page =>  -1) if the count is less than 50, last page
                $page = (count($results) == 50) ? $page + 1 : $page - ($page+1); 
                foreach ($results as $timeEntry) {
                    if($this->getTimeEntryById($timeEntry->getBcId())) {
                        //stop iteration if the time entry is already loaded
                        if(!$reloadAll) { $page = -1; } 
                    } else {
                        array_push($this->_timeEntries, $timeEntry);
                    }
                }
            }
        }        
        
        return $this->_timeEntries;
    }

    /**
     * Calculate the start date of the project
     * 
     * @param int $ts
     * @return type 
     */
    public function calculateStartDate($ts)
    {
        while(date('N', $ts) != 1) {
            $ts -= 86400;
        }
        return date('Y-m-d', $ts);
    }
    
    /**
     * Calculate end date for the project
     * @param int $ts
     * @return type 
     */
    public function calculateEndDate($ts)
    {
        while(date('N', $ts) != 6) {
            $ts -= 86400;
        }
        return date('Y-m-d', $ts);
    }

    /**
     * Create a new project in Basecamp with $title
     * @todo this is to be complted with the functionality as the current API 
     * is not able to perform creating a new project in Basecamp
     * 
     * @param string $title
     * @return bool 
     */
    public function createNewBcProject($title)
    {
        $respXml = $this->getBcConnect()->createProject($title);
        if(empty ($respXml['body'])) { return false; }
        
        $_respXml = simplexml_load_string($respXml['body']);
        if(!$_respXml->id) { return false; }
        
        //parse the project xml into array
        $data = $this->_getHelper()->XMLToArray($_respXml);
        $project = $this->loadProjectFromBcData($data);
        
        return $project->getId() ? $project : false;
    }

    /**
     * Save the project data submitted from the project edit form
     * called from Project_Controller_CreateController::saveAction
     * Marketing lead and the dev lead are submitted along with the data
     * 
     * @param type $data 
     */
    public function saveProject($data)
    {
        //if the project doesnot exists create a new project in Basecamp
        if(!$this->getId()) {
            $this->setTitle($data['title']);
            $project = $this->createNewBcProject($data['title']);
            if(!$project) { return false; }
            
            $this->load($project->getId());
        }
        $this->setMLead($data['m_lead']);
        $this->setDLead($data['d_lead']);
        
        $this->save();
        //add the project to the users loaded projects list
        App_Main::getSession()->getUser()->addProjectToStack($this);
        
        
        /*//process the milestone data passed from the form        
        if(!empty($data['milestones']) && is_array($data['milestones'])) {
            $milestones = array();
            foreach($data['milestones'] as $msInfo) {
                $msSaved = $this->saveMilestone($msInfo['title'], 
                                                $msInfo['date'], 
                                                $msInfo['user'], 
                                                $msInfo['type'], 
                                                @$msInfo['bc_id']);
                if(!$msSaved) { continue; }
                $milestones[] = $msSaved;
            }
            //$this->_milestones = $milestones;
        }*/
        return $this;
    }
    
    /**
     * Save a milestone and create/update it in Basecamp
     * 
     * @param string $title  Milestone title
     * @param string $date  Milestone date
     * @param int $user  Milestone responsible user
     * @param string $type  Milestone type (M|D)
     * @param int $bcId  milestone Basecamp id
     * @return type 
     */
    public function saveMilestone($title, $date, $user, $type, $bcId = null)
    {
        $mode = 'edit';
        if(!empty($bcId)) {
            $milestone = $this->getMilestoneById($bcId);
        }
        if(empty ($milestone)) {
            $milestone = App_Main::getModel('project/milestone');
            $milestone->setProjectId($this->getBcId());
            $mode = 'add';
        }
        $milestone = $milestone->saveMilestone($title, 
                                         $this->_getHelper()->formatDateFromJs($date), 
                                         $user, 
                                         $type);
        if($milestone && $mode == 'add') {
            $this->_milestones[] = $milestone;
        }
        return $milestone;
    }

    /**
     * Get milestone by the milestone identifier (dafault is milestone Basecamp id)
     * 
     * @param int $id
     * @param string $field
     * @return Project_Model_Milestone 
     */
    public function getMilestoneById($id, $field = 'bc_id', $milestones = array())
    {
        $milestones = !empty ($milestones) && is_array($milestones) ? $milestones : $this->getMilestones();
        foreach($milestones as $milestone) {
            if(!$milestone instanceof Project_Model_Milestone) { continue; }
            if($milestone->getData($field) == $id) { return $milestone; }
        }
        return false;
    }
    
    /**
     * Get todolist by the todolist identifier (dafault is todolist Basecamp id)
     * 
     * @param int $id
     * @param string $field
     * @return Project_Model_Todolist 
     */
    public function getTodolistById($id, $field = 'bc_id', $todolists = array())
    {
        $todolists = !empty ($todolists) && is_array($todolists) ? $todolists : $this->getTodolists();
        foreach($todolists as $todolist) {
            if(!$todolist instanceof Project_Model_Todolist) { continue; }
            if($todolist->getData($field) == $id) { return $todolist; }
        }
        return false;
    }
    
    /**
     * Get time-entry by the time-entry identifier (dafault is time-entry Basecamp id)
     * 
     * @param int $id
     * @param string $field
     * @return Project_Model_Time 
     */
    public function getTimeEntryById($id, $field = 'bc_id', $fromLocal = false)
    {
        foreach($this->getTimeEntries(false, false) as $timeEntry) {
            if(!$timeEntry instanceof Project_Model_Time) { continue; }
            if($timeEntry->getData($field) == $id) { return $timeEntry; }
        }
        return false;
    }
    
    /**
     * Get the total time registered for a todo identified by the todo Basecamp id
     * 
     * @param int $bcId
     * @return int 
     */
    public function getTimeEntryForTodo($bcId)
    {
        $hours = 0;
        foreach($this->getTimeEntries() as $timeEntry) {
            $todoId = $timeEntry->getTodoId();
            if(!$todoId || $todoId != $bcId) { continue; }
            $hours += $timeEntry->getHours(); 
        }
        return $hours;
    }
    
    /**
     * Get the project Basecamp landing url
     * 
     * @return string project url 
     */
    public function getProfileUrl()
    {
        return trim($this->getBcConnect()->getBaseurl(), '/') .'/projects/'. $this->getBcId() .'/log';
    }
    
    /**
     * Set the flag to reload all the data from Basecamp 
     * (project info, milestone, todolist, todo, time-entry)
     * 
     * @return Project_Model_Project 
     */
    public function setBcFullReload()
    {
        //set the milestone and todolist loaded time to a previous day so that it gets refreshed from server
        $this->getResource()->setBcFullReload($this);
        return $this;
    }

    /**
     * Prepare the project data array for passing in JSON format
     *  - id
     *  - title
     *  - bc_id
     *  - leads [m]
     *  - leads [d]
     *  - start_date
     *  - end_date
     * IF $incTime include time-entries @todo not implemented
     * IF $incMilestonee include milestone and todolist information
     *  Milestone 
     *      - title
     *      - type
     *      - user
     *      - date
     *      - bc_id
     *      - todo_stats
     *              - lists
     *              - count (total todos)
     *              - completed
     *              - uncomplted
     *              - comments
     *              - hours
     *      
     * 
     * @param bool $incMilestone
     * @param bool $incTime
     * @return type 
     */
    public function prepareDataForJson($incMilestone = false, $incTime = false, $refreshBc = false)
    {
        if(!$this->getId()) { return false; }
        
        $data = array();
        $data['id'] = $this->getId();
        $data['title'] = $this->getTitle();
        $data['bc_id'] = $this->getBcId();
        $data['bc_link'] = $this->getProfileUrl();
        $data['leads']['m'] = $this->getMLead();
        $data['leads']['d'] = $this->getDLead();
        $data['start_date'] = App_Main::getHelper('stages')->formatDateForJs(strtotime($this->getStartDate()));
        $data['end_date'] = App_Main::getHelper('stages')->formatDateForJs(strtotime($this->getEndDate()));
        
        if($incTime){
            $times = $this->getTimeEntries();            
        }
        if($incMilestone) {
            $milestones = $this->getMilestones($refreshBc);
            $todoLists = $this->getTodoLists($refreshBc);
            if(!$todoLists) { $todoLists = array(); }
            $todos = $this->getTodos($refreshBc); //todos are loaded to record the time entries and comments, other stats like total, completed, uncompleted are retrived along with todolist 
            
            if(empty($milestones) && !$refreshBc) { $milestones = $this->getMilestones(true); }            
            if(!empty($milestones) && empty($todoLists) && !$refreshBc) { $todoLists = $this->getTodoLists(true); }
            
            $msArray = array();            
            foreach($milestones as $milestone) {
                $msData = $milestone->prepareDataForJson();

                //add todo list status
                $todoData = array('lists'=>0, 'count'=>0,'completed'=>0,'uncompleted'=>0, 'comments'=>0, 'hours'=>0);
                foreach ($todoLists as $todoList) {
                    //continue if the todolist is not assiged under the current processed milestone
                    if($milestone->getBcId() != $todoList->getMilestoneId()) { continue; }
                    $todoData['lists'] += 1;
                    $todoData['count'] += $todoList->getTodoCount();
                    $todoData['completed'] += $todoList->getTodoCompleted();
                    $todoData['uncompleted'] += $todoList->getTodoUncompleted();
                    //add the todo comments and hour status
                    $todos = $todoList->getTodos();
                    //add the todo specific info into the todo stats (total comments and total hours)
                    if(!empty ($todos) && is_array($todos)) {
                        foreach($todos as $todo) {
                            $todoData['comments'] += $todo->getCommentCount();
                            $todoData['hours'] += $this->getTimeEntryForTodo($todo->getBcId()); //$todo->getTimeEntry();
                        }
                    }
                }
                $msData['todo_stats'] = $todoData;
                $msData['ms_status_display'] = App_Main::getHelper('stages')->processMilestoneStats($milestone, $todoData);
                $msArray[] = $msData;
            }
            $data['milestones'] = $msArray;
        }
        return $data;
    }
}
?>