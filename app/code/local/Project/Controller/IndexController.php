<?php
/**
 * class Project_Controller_IndexController
 * 
 * @package Project
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Controller_IndexController extends Core_Controller_Action
{
    /**
     * Check for a valid user session
     * 
     * @return type 
     */
    private function isUserLoggedIn()
    {
        return (bool)$this->_getSession()->getUser();
    }
    
    public function indexAction()
    {
        if(!$this->isUserLoggedIn()) { return $this->_redirect(''); }
        
        $this->getLayout()->getBlock('root')->addBodyClass('projects logged-in');
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'stages/user/projects.phtml'));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');

        $this->renderLayout();
        
    }

    /**
     * Load a project informaiton with JSON formated
     * 
     * @return type 
     */
    public function loadAction()
    {
        if(!$this->isUserLoggedIn()) { return $this->_redirect(''); }
        
        $projectId = $this->getRequestParam('project_bc_id');
        if(!$project = App_Main::getSession()->getUser()->getProjectById($projectId, 'bc_id')) {
            $project = App_Main::getModel('project/project')->load($projectId, 'bc_id');
        }
        echo Zend_Json::encode(array('success'=>1, 'project'=>$project->prepareDataForJson(true, true)));
    }

    /**
     * Load the project view block
     * 
     * @return type 
     */
    public function viewAction()
    {
        if(!$this->isUserLoggedIn()) { return $this->_redirect(''); }

        $projectId = $this->getRequestParam('id');
        $project = $this->_getSession()->getUser()->getProjectById($projectId, 'bc_id');
        $this->getLayout()->getBlock('root')->addBodyClass('projects logged-in');
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'stages/project/view.phtml', 'project'=>$project));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');

        $this->renderLayout();
    }


    /**
     * Tmp function to create project action
     */
    public function createBcProjectAction()
    {
        App_Main::getModel('project/project')->getBcConnect()->createProject('First Stages Project');
    }

    /**
     * Tmp function to create milestone
     */
    public function create_msAction()
    {
        App_Main::getModel('project/project')->getBcConnect()
                                    ->createMilestoneForProject(6795309,
                                                              'Test Milestone',
                                                              '2011-04-22',
                                                              'person',
                                                              5542386
                                        );
    }

    /**
     * Tmp function to load the projects
     */
    public function load_projectsAction()
    {
        $result = App_Main::getModel('stages/user')->getProjects();
    }

    /**
     * Tmp function to load the todolist
     */
    public function load_todolistAction()
    {
        $result = App_Main::getModel('project/todolist')->loadTodolistFromBc(6236545);
    }
    
    /**
     * Tmp function to load the time-entries
     */
    public function load_time_entryAction()
    {
        $result = App_Main::getModel('project/time')->loadTimeFromBc(5223184);
    }
}
?>
