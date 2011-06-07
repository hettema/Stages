<?php
/**
 * class Backend_Controller_IndexController
 * 
 * @package Backend
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Controller_IndexController extends Admin_Controller_Action
{
    /**
     * Backend Index action, shows the dashboard by default
     */
    public function indexAction()
    {
        $this->_redirect('backend/visitor');
    }
    
    /**
     * Render the page not found html
     */
    public function norouteAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Page Not Found', false);

        $noRoute = $this->getLayout()->createBlock('backend/template', 'not-found', array('template'=>'page/not_found.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'not-found');
        $this->renderLayout();
    }

    /**
     * Show the 'No Access' page when there is a access violation
     */
    public function no_accessAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Page Not Accessible', false);

        $noRoute = $this->getLayout()->createBlock('backend/template', 'no-access', array('template'=>'page/no_access.phtml'));
        
        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'no-access');
        $this->renderLayout();
    }
    
    /**
     * Display the backend dashboard
     */
    public function dashboardAction()
    {
         $this->getLayout()->getBlock('head')->setTitle('Admin Dashboard', false);
        
        $noRoute = $this->getLayout()->createBlock('backend/template', 'dashboard', array('template'=>'dashboard/home.phtml'));

        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'dashboard');
        $this->renderLayout();
    }
}
?>
