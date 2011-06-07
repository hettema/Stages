<?php
/**
 * class Backend_Controller_DebugController
 * 
 * @package Backend
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Controller_DebugController extends Admin_Controller_Action
{
    public function emailAction()
    {
        $header = $this->getLayout()->getBlock('header')->setTemplate(false);
        $contentMain = $this->getLayout()->createBlock('core/template', 'content-main', array('template'=>'debug/email.phtml'));
        $this->getLayout()->getBlock('content')->append($contentMain, 'content-main');
        $this->renderLayout();
    }
    public function email_submitAction()
    {
        $request = $this->getRequest();
        $email_template = $request->getParam('email_template') ? $request->getParam('email_template') : false;
        $websiteId = $request->getParam('website') ? $request->getParam('website') : false;
        $website = App_Main::getModel('core/website');
        if($websiteId) {
            $website->load($websiteId);
        }
        if(empty($email_template)) {
            echo 'Choose a template file to test';
            return;
        }
        $email = App_Main::getModel('core/email');
        $email->setFromEmail(App_Main::DEFAULT_MAIL_SENDER);
        $email->setFromName(App_Main::DEFAULT_MAIL_SENDER_NAME);
        $email->setType('html');
        $email->setLocale($website->getLocale());
        $email->setTheme($website->getTheme());
        $email->setTemplate($email_template);
        $email->setToEmail('recipient@hisdomain.com');
        $email->setToName('recipient name');
        $email->setSubject('Email debugger :: Testing email');
        $email->setTemplateVar(array('name'=> 'recipient name',
                                    'email'=> 'recipient@hisdomain.com',
                                    'website_url'=>$website->getBaseUrl('web'),
                                    'status'=>'status',
                                    'comments'=>'Notification comments'                                    
                                   )
                             );


        echo $email->getBody();

    }

    
}
?>
