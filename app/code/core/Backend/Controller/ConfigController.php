<?php
/**
 * class Backend_Controller_ConfigController
 * 
 * @package Backend
 * @subpackage Config
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Controller_ConfigController extends Admin_Controller_Action
{
    private function updateLayout()
    {
        $tmplAction = $this->getLayout()->createBlock('backend/template', 'menu-content', array('template'=>'settings/content_header.phtml'));
        $this->getLayout()->getBlock('content')->append($tmplAction, 'menu-content');
    }
    
    /**
     * View the config settings page
     */
    public function indexAction()
    {
        $this->updateLayout();
        $this->getLayout()->getBlock('head')->setTitle('Core Settings', false, true);

        $tmplContent = $this->getLayout()->createBlock('backend/settings_edit', 'main-content', array('template'=>'settings/edit.phtml'));
        $this->getLayout()->getBlock('content')
                                ->append($tmplContent, 'main-content');
        $this->renderLayout();
    }

    /**
     * Save the config options values
     */
    public function submitAction()
    {
        $request = $this->getRequest();
        $scope = $request->getParam('scope') ? $request->getParam('scope') : 'default';
        $scopeId = $request->getParam('scope_id') ? $request->getParam('scope_id') : 0;
        $configData = $request->getParam('config_data');
        
        //checkboxes should be checked for unchecked ones
        if(!isset($configData['web-secure-backend'])) {
            $configData['web-secure-backend'] = 0;
        }

        App_Main::getConfig()->saveConfigData($configData, $scope, $scopeId);
        $websiteCode =  $this->getRequest()->getParam('config-website');
        $this->_redirect('backend/config/index', array('website'=>$websiteCode));
    }

    ### Website management ###
    /**
     * List all the websites 
     */
    public function websitesAction()
    {
        $this->updateLayout();
        $this->getLayout()->getBlock('head')->setTitle('Core Websites', false, true);

        $tmplContent = $this->getLayout()->createBlock('backend/template', 'main-content', array('template'=>'settings/website/list.phtml'));
        $this->getLayout()->getBlock('content')->append($tmplContent, 'main-content');
        $this->renderLayout();
    }

    /**
     * Add a new website
     * @return type 
     */
    public function website_addAction()
    {
        $websiteCode = $this->getRequest()->getParam('website_code');
        $website = App_Main::getModel('core/website');
        if(!$website->validateWebsiteCode($websiteCode)) {
            App_Main::getSession()->addError('The website code you have specified is invalid or is already existing. Please try a new one code');
            $this->_redirect('backend/config/websites');
            return;
        }
        $website->setData('code', $websiteCode);
        $website->setData('name', $this->getRequest()->getParam('website_name'));
        $website->save();
        $this->_redirect('backend/config/index', array('website'=>$website->getId()));
    }
}
?>
