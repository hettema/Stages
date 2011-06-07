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

        $this->getLayout()->getBlock('root')->addBodyClass('home logged-in');
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'stages/project/create.phtml'));

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
        $data = $this->getRequestParam('project');
        if(!empty($data['bc_id'])) {
            $project = $this->_getSession()->getUser()->getProjectById($data['bc_id']);
        }
        if(empty($project)) {
            $project = App_Main::getModel('project/project');
        }
        $project->saveProject($data);
        return $this->_redirectUrl(App_Main::getUrl('project/index/view'). '?id='. $project->getBcId());
        echo 'saved';
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
