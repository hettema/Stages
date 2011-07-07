<?php
/**
 * class Project_Controller_CreateController
 * 
 * @package Project
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Controller_CreateController extends Core_Controller_Action
{
    public function indexAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Create Project', false);
        
        $projectId = $this->getRequestParam('id');
        if($projectId) {
            $project = $this->_getSession()->getUser()->getProjectById($projectId, 'bc_id');
        } else {
            $project = false;
        }
        
        $this->getLayout()->getBlock('root')->addBodyClass('add logged-in');
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'stages/project/create.phtml', 'project'=>$project));

        $this->getLayout()->getBlock('content')
                                ->append($contentMain, 'content-main');
        $this->renderLayout();
    }

    /**
     * Save the project info
     * 
     * @return type 
     */
    public function saveAction()
    {
        if(!$this->isUserLoggedIn()) { echo Zend_Json::encode(array('redirect'=>  App_Main::getUrl(''))); return; }
        
        $data = array();
        $data['m_lead'] = $this->getRequestParam('m_lead');
        $data['d_lead'] = $this->getRequestParam('d_lead');
        $bcId = $this->getRequestParam('project_bc_id');
        if($bcId) {
            $project = $this->_getSession()->getUser()->getProjectById($bcId);
        } else if($title = $this->getRequestParam('project_title')) {
            $data['title'] = $this->getRequestParam('project_title');
            $project = App_Main::getModel('project/project');
        }
        
        if($project && $project->saveProject($data)) {
            $refrshBc = !empty($bcId);
            echo Zend_Json::encode(array('success'=>1, 'project'=>$project->prepareDataForJson(true, true, $refrshBc)));
        } else {
            echo Zend_Json::encode(array('success'=>0, 'message'=>'Error saving the project'));
        }
        //return $this->_redirectUrl(App_Main::getUrl('project/index/view'). '?id='. $project->getBcId());
        return false;
    }

    /**
     * Save the project leads called from ajax
     * 
     * @return type 
     */
    public function save_leadAction()
    {
        if(!$this->isUserLoggedIn()) { echo Zend_Json::encode(array('redirect'=>  App_Main::getUrl(''))); return; }
        
        $success = 0;
        $projectId = $this->getRequestParam('project_id');
        $project = $this->_getSession()->getUser()->getProjectById($projectId);
        if($project && $project->getId()) {
            $project->setMLead($this->getRequestParam('m_lead'));
            $project->setDLead($this->getRequestParam('d_lead'));
            if($project->save()) {
                $success = 1;
            }
        }
        echo Zend_Json::encode(array('success'=>$success));
    }
    
    /**
     * Save the milestone under the loaded project
     * called via ajax request
     * 
     * @return type 
     */
    public function save_milestoneAction()
    {
        if(!$this->isUserLoggedIn()) { echo Zend_Json::encode(array('redirect'=>  App_Main::getUrl(''))); return; }
        
        $success = 0;
        $projectId = $this->getRequestParam('project_id');
        if($projectId) {
            $project = $this->_getSession()->getUser()->getProjectById($projectId);
            if($project && $project->getId()) {
                $title = $this->getRequestParam('title');
                $date = $this->getRequestParam('date');
                $user = $this->getRequestParam('user');
                $type = $this->getRequestParam('type');
                $bcId = $this->getRequestParam('bc_id');
                $milestone = $project->saveMilestone($title, $date, $user, $type, $bcId);
                if($milestone) {
                    $success = 1;
                    echo Zend_Json::encode(array('success'=>$success, 'milestone'=>$milestone->prepareDataForJson()));
                    return;
                }
            }
        }
        echo Zend_Json::encode(array('success'=>$success));
    }
    
    /**
     * Check for a valid user session
     * @return type 
     */
    private function isUserLoggedIn()
    {
        return (bool)$this->_getSession()->getUser();
    }
}
?>
