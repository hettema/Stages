<?php
/**
 * class Backend_Model_Eav_Edit
 * 
 * @package Core
 * @subpackage Eav
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Model_Eav_Edit extends Core_Model_Eav
{
    /**
     *
     * @var string add|edit 
     */
    var $mode = 'add';
    
    var $entityId;
    
    var $entity = false;
    
    var $eavAttributes = array();
    var $eavAttributeOptionValues = array();
    
   
    protected function _construct()
    {
        $this->_init('core/eav');
    }
    
    /**
     * Get the entity model by entiry id
     * 
     * @param int $entityId
     * @return Core_Model_Abstract 
     */
    private function loadEntity($entityId)
    {
        $entityInfo = $this->getResource()->getEntityInfo($entityId);
        
        if(!empty($entityInfo)) {
            $this->entity = App_Main::getModel($entityInfo['entity_model']);
            $this->entity->load($entityId);
        }
        return $this->entity;
    }    
    
    /**
     * Load the eav attributes for the entity type id
     * 
     * @param int $entityTypeId
     * @return Backend_Model_Eav_Edit 
     */
    private function loadAttributes($entityTypeId)
    {
        $excludeAttributes = array();
        $eavAttributes = $this->_getResource()->getEavAttributes($entityTypeId);
        
        if(!$eavAttributes) return false;
        $this->eavAttributes = $eavAttributes;
        $this->loadAttributeOptionValues();
        
        return $this;
    }

    /**
     * Load the attribute option values for the current loaded attributes
     * 
     * @return Backend_Model_Eav_Edit 
     */
    private function loadAttributeOptionValues()
    {
        $attributeIds = array();
        foreach($this->eavAttributes as $attribute) {
            if($attribute['frontend_input']== 'select' || $attribute['frontend_input'] == 'multipleselect') {
                $attributeIds[] = $attribute['attributeId'];
            }
        }
        if(!empty($attributeIds)) {
            $$this->eavAttributeOptionValues = $this->getResource()->getAttributeOptionValues($attributeIds);
        }
        return $this;
    }
    
    /**
     * Get the eav attribute xml for the entity type id
     * used while loading the edit form 
     * 
     * @param int $entityTypeId
     * @param SimpleXMLElement $xmlObj
     * @return string  
     */
    public function getEavAttributeXml($entityTypeId, SimpleXMLElement $xmlObj = null)
    {
        return $this->prepareEavAttributeXml($entityTypeId, xmlObj);
    }
         
    /**
     * Prepare the xml for the eav attributes 
     * 
     * @param int $entityTypeId
     * @param SimpleXMLElement $xmlObj
     * @return type 
     */
    public function prepareEavAttributeXml($entityTypeId, SimpleXMLElement $xmlObj = null)
    {
        $this->loadAttributes($entityTypeId);
        if(empty ($xmlObj)) {
            $xmlObj = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root><eav_attributes/></root>');
        }
        $xmlObj->eav_attributes->addAttribute('entity_type_id',$entityTypeId);
        $currAttribute = null;
        foreach ($this->eavAttributes as $attrtibute) {

            if($currAttribute != $attrtibute['attribute_id']) {
                $currAttribute = $attrtibute['attribute_id'];
                $attributeNode = $xmlObj->eav_attributes->addChild( $attrtibute['attribute_code'], $this->replaceXmlSpecialChars($attrtibute['frontend_label']));
                $attributeNode->addAttribute('id',$attrtibute['attribute_id']);

                //set the attribute specific properties into the node
                $attributeInfoNode = $attributeNode->addChild('attribute_info');
                foreach ($attrtibute as $field=>$value) {
                    if(empty($value) || is_array($value)) { continue; }

                    $attributeInfoNode->addChild($field, $this->replaceXmlSpecialChars($value));
                }
                //set the attribute option values
                if(!empty($this->eavAttributeOptionValues[$currAttribute])) {
                    $attributeOptionValueNode = $attributeNode->addChild('option_values');
                    foreach($this->eavAttributeOptionValues[$currAttribute] as $valueId=>$value) {
                        $attributeOptionValueNode->addChild('_' . $valueId, $this->replaceXmlSpecialChars($value))
                                                                                      ->addAttribute('id', $valueId);
                    }
                }
            }         
        }
        $str = (string)$xmlObj->asXML();
        return preg_replace('/\<\?.*\?\>\n/','',$str);
    }
    
    /**
     * Get the entity XML for the entity id
     * @param int $entityId
     * @return string 
     */
    public function getEntityXml($entityId)
    {
        $entity = $this->loadEntity($entityId);
        if(!$entity) return '';
        
        $xmlObj = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root><entity_info/></root>');
        $entityEavInfoNode = $xmlObj->entity_info->addChild('entity_eav');

        $_data = $this->entity->getData();
        foreach($_data as $field=>$value) {
            $entityEavInfoNode->addChild($field, $this->replaceXmlSpecialChars($value));
        }
        $str = (string)$xmlObj->asXML();
        return preg_replace('/\<\?.*\?\>\n/','',$str);
    }
    
    /**
     * Replace the special charactes from the XML string
     * 
     * @param string $string
     * @return string 
     */
    private function replaceXmlSpecialChars($string)
    {
        return preg_replace('/&(?!\w+;)/', '&amp;',$string);
    }
     
    /**
     * Get the entity attribute values as JSON formated string
     * @return type 
     */
    private function getEntityJSON()
    {
        if(!$this->entityId) { return false; }
        if(!$this->entity) { return false; }

        $entityEavInfo = array();
        $_data = $this->entity->getData();
        foreach ($_data as $field=>$value) {            
            $entityEavInfo[$field] = $value;
        }
        return json_encode($entityEavInfo);
    }
    
    /**
     * Saves the eav data speecific to the entity or entity type id specified
     * 
     * @param string $xmlData
     * @param int $entityTypeId
     * @return Core_Model_Abstract
     */
    public function submitXmlEavData($xmlData, $entityTypeId = null)
    {
        $xml = new SimpleXMLElement($xmlData);

        $entityNode = $xml->entity;
        if($entityNode['entityTypeId']) {
            $entityTypeId = (string)$entity['entityTypeId'];
        }

        //initiate the model for the entity and update the submitted entity
        if(!empty($entityNode['entity_id'])) {
            $entityId =  intval((string)$entityNode['entity_id']);
            $entityId = is_int($entityId) ? $entityId : null;
            if(!empty($entityId)) {
                $entity = $this->loadEntity($entityId);
            }
        }
        //initiate the model for the entity type and save it as a new entity
        if((empty($entity) || !$entity instanceof Core_Model_Abstract) && !empty($entityTypeId)) {
            $entityModel = $this->getResource()->getEntityModel($entityTypeId);
            if(!empty($entityModel)) {
                $entity = App_Main::getModel($entityModel);
                $entity->setEntityTypeId($entityTypeId);
            }
        }

        if(empty($entity)) {
            App_Main::getSession()->addError('Unable to load the entity model for the submitted data.');
            return false;
        }

        //Assign the attribute values into the entity object prior to saving
        foreach($entityNode->children() as $childNode) {

            $backend = (string)$childNode['backendType'];
            $attributeCode = (string)$childNode->getName();
            $attributeId = (string)$childNode['attributeId'];
            $entity->setData($attributeCode, (string)$childNode);
        }
        $entity->setUpdatedAt(now());
        $entity->save();
        
        return $entity;
    }

    /**
     * Check the unique values among the selected entity type and attributes from the submitted xml string
     * 
     * @param string $xmlData
     * @return type 
     */
    public function checkUniqueXmlEavData($xmlData)
    {
        $xml = new SimpleXMLElement($xmlData);
        
        if($xml->eav_check_unique) {
        $dataNode = $xml->eav_check_unique;
        }
        $entityTypeId = (string)$dataNode['entity_type_id'];
        
        $entityId = $dataNode['entity_id'] ? intval((string)$dataNode['entity_id']) : false;

        $returnXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root><check_unique_response/></root>');
        $errors = 0;

        //iterate over the attribute and values
        foreach($dataNode->children() as $childNode) {
            $backendType = (string)$childNode['backend_type'];
            $attributeCode = (string)$childNode->getName();
            $attributeId = intval((string)$childNode['attribute_id']);
            $value = (string)$childNode;

            if($this->getResource()->checkUnique($attributeId, $value, $backendType, $entityTypeId, $entityId)) {
                $attributeNode = $returnXml->check_unique_response->addChild($attributeCode, $this->replaceXmlSpecialChars($value));
                $errors++;
            }
        }
        $returnXml->check_unique_response->addAttribute('errors', $errors);
        $str = (string)$returnXml->asXML();
        return preg_replace('/\<\?.*\?\>\n/','',$str);
    }
}
?>
