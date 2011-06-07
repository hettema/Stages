<?php
/**
 * class Core_Controller_IndexController
 * 
 * @package Core
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_IndexController extends Core_Controller_Action
{
    public function norouteAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Page Not found', false);

        $noRoute = $this->getLayout()->createBlock('core/template', 'no-route', array('template'=>'page/not_found.phtml'));
        
        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'no-route');
        $this->renderLayout();
    }

    public function no_accessAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Page Not Accessible', false);

        $noRoute = $this->getLayout()->createBlock('core/template', 'no-access', array('template'=>'page/no_access.phtml'));
        
        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'no-access');
        $this->renderLayout();
    }

    public function indexAction()
    {        
        return App_Main::getControllerInstance('stages/index')->indexAction();
    }
}
?>
