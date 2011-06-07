<?php
/**
 * class Backend_Controller_EavController
 * 
 * @package Backend
 * @subpackage Eav
 * @category Controller
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Controller_EavController extends Admin_Controller_Action
{
    
    /** 
     * Load the Eav-Xml for the entity specified
     *
     * @param int $entityTypeId
     * @param int $entityId
     * @param type $xmlObj
     * @return type 
     */
    public function load_eav_xmlAction($entityTypeId = null, $entityId = null, $xmlObj = null)
    {
        if(!$entityId && $this->getRequest()->getParam('entityId')) {
            $entityId = $this->getRequest()->getParam('entityId');
        }
        if(empty ($entityTypeId) && empty ($entityId)) {
            return false;
        }
        
        if(!empty ($entityTypeId)) {
            $eavLoader = App_Main::getModel('backend/eav_edit');
            $eavXml = $eavLoader->getEavAttributeXml($entityTypeId, $xmlObj);
            
            if($entityId) {
                $entityEavXml = $eavLoader->getEntityXml($entityId);
                $eavXml .= $entityEavXml;
            }
            
            $this->getResponse()->setHeader('Content-Type', 'text/xml');
            $this->getResponse()->setBody('<response_root>'. $eavXml .'</response_root>');
        }
    }

    /**
     * Save the EAV entity data XML
     *
     * @param int $entityTypeId
     * @param bool $returnObj
     * @return mixed 
     */
    public function submit_eav_xmlAction($entityTypeId = null, $returnObj = false)
    {        
        $data = $this->getRequest()->getParam('eav_data');
        if(!empty($data)) {
            $eavLoader = App_Main::getModel('backend/eav_edit');
            $entity = $eavLoader->submitXmlEavData($data, $entityTypeId);
        }
        
        if($returnObj) { return $entity; }
        
        if($this->getRequest()->isXmlHttpRequest() && empty($entity)) {
            App_Main::getSession()->addError('Error saving data and entity. Entity not defined');
        } else {
            $this->getResponse()->setHeader('Content-Type', 'text/xml');
            return $this->load_eav_xmlAction($entity->getEntityTypeId(), $entity->getId());
        }
    }

    /**
     * Check for unique attribute values
     * Queried over ajax
     */
    public function check_uniqueAction()
    {
        $data = $this->getRequest()->getParam('check_data');
        if(!empty($data)) {
            $eavEdit = App_Main::getModel('backend/eav_edit');
            $response = $eavEdit->checkUniqueXmlEavData($data);
        }
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHeader('Content-Type', 'text/xml');
            $this->getResponse()->setBody('<response_root>'. $response .'</response_root>');
        } else {
            //code for html display..
        }
    }
    
}
?>
