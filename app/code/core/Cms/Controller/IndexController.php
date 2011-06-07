<?php
/**
 * class Cms_Controller_IndexController
 * 
 * @package Cms
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Cms_Controller_IndexController extends Core_Controller_Action
{
    /**
     * Action called when the requested route not found
     */
    public function norouteAction()
    {
        $this->getLayout()->getBlock('head')->setTitle('Page Not found', false);
        $noRoute = $this->getLayout()->createBlock('core/template', 'no-route', array('template'=>'page/not_found.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($noRoute, 'no-route');
        $this->renderLayout();
    }

    /**
     * Cms index action, default action for the homepage
     */
    public function indexAction()
    {
        $this->getLayout()->getBlock('head')->setTitle(App_Main::DEFAULT_TITLE, false);

        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'cms/home.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($contentMain, 'content-main');
        $this->renderLayout();
    }
}
?>
