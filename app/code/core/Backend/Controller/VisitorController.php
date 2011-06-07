<?php
/**
 * class Backend_Controller_VisitorController
 * 
 * @package Backend
 * @subpackage Visitor
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Controller_VisitorController extends Admin_Controller_Action
{
    /**
     * List the visitors and filters
     */
    public function indexAction()
    {
        $contentMain = $this->getLayout()->createBlock('visitor/list', 'content-main', array('template'=>'visitor/list.phtml'));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');
        $this->renderLayout();
    }

    /**
     * List the visitor urls
     */
    public function visitor_urlsAction()
    {
        $visitorId = $this->getRequest()->getParam('visitorId');
        $urlsBlock = $this->getLayout()->createBlock('visitor/list', 'content-main', array('template'=>'visitor/list_urls.phtml'));
        $return = array('visitorId'=>$visitorId,
                        'url_html'=>$urlsBlock->toHtml());
        echo Zend_Json::encode($return);
    }
}
