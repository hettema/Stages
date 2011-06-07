<?php
/**
 * class Core_Model_Eav 
 * EAV model to manage the entity specific data management
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Eav extends Core_Model_Abstract
{

    var $eavAttributes = array();
    
    var $eavAttributeOptionValues = array();

    protected function _construct()
    {
        $this->_init('core/eav');
    }
    
    /**
     * Load the entity object with the entity id
     * The entity object will loaded form the entity table and the eav will be loaded
     * 
     * @param int $entityId
     * @return pbject entity model 
     */
    public function loadEntity($entityId)
    {
        $entityInfo = $this->getResource()->getEntityInfo($entityId);

        if(!empty($entityInfo)) {
            $entity = App_Main::getModel($entityInfo['entity_model']);
            $entity->load($entityId);
        }
        return $entity;
    }

    /**
     * Load eav data for the entity object
     * 
     * @param Core_Model_Abstract $object
     * @return Core_Model_Abstract 
     */
    public function loadEavData(Core_Model_Abstract $object)
    {
        $this->_getResource()->loadEavData($object);
        return $object;
    }

    /**
     * Set the eav value tables
     * 
     * @param array $eavTables
     * @return type 
     */
    public function setEavTables(array $eavTables)
    {
        return $this->_getResource()->setEavTables($eavTables);
    }

    /**
     * Load only values of a set of attributes for the given entity object
     * 
     * @param Core_Model_Object $object
     * @param type $attributes
     * @return Core_Model_Object 
     */
    public function loadSpecificEavData(Core_Model_Object $object, $attributes)
    {
        $attributes = !is_array($attributes) ? array($attributes) : $attributes;
        $this->getResource()->loadEavData($object, $attributes);
        return $object;
    }

    /**
     * Load all the defined attributes of the entity model
     * @param type $entityTypeId
     * @return Core_Model_Eav 
     */
    protected function loadAttributes($entityTypeId)
    {
        $excludeAttributes = array();
        $eavAttributes = $this->_getResource()->getEavAttributes($entityTypeId);

        if(!$eavAttributes) return false;
        $this->eavAttributes = $eavAttributes;
        $this->loadAttributeOptionValues();

        return $this;
    }
    
    /**
     * Get attrbutes which are set to be visible to the user
     * 
     * @param type $entityTypeId
     * @return array attributes 
     */
    public function getDisplayAttributes($entityTypeId)
    {
        $attributes = $this->_getResource()->getEavAttributes($entityTypeId);
        $return = array();
        foreach($attributes as $attribute) {
            if($attribute['is_visible'] !=1) { continue; }
            $return[] = $attribute;
        }
        return $return;
    }

    /**
     * Load the attribute option values
     * 
     * @return Core_Model_Eav 
     */
    protected function loadAttributeOptionValues()
    {
        $attributeIds = array();
        foreach($this->eavAttributes as $attribute) {
            if($attribute['frontend_input']== 'select' || $attribute['frontend_input'] == 'multipleselect') {
                $attributeIds[] = $attribute['attribute_id'];
            }
        }
        if(!empty($attributeIds)) {
            $this->eavAttributeOptionValues = $this->getResource()->getAttributeOptionValues($attributeIds);
        }
        return $this;
    }

    /**
     * Get the entity object XML
     * Used to display and edit the entity object via javascript
     * 
     * @param type $entityId
     * @return type 
     */
    public function getEntityXml($entityId)
    {
        $entity = $this->loadEntity($entityId);
        if(!$entity) return '';

        $xmlObj = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root><entity_info/></root>');
        $entityEavInfoNode = $xmlObj->entity_info->addChild('entity_eav');

        $_data = $entity->getOrigData();
        foreach($_data as $field=>$value) {
            $entityEavInfoNode->addChild($field, $this->replaceXmlSpecialChars($value));
        }
        $str = (string)$xmlObj->asXML();
        return preg_replace('/\<\?.*\?\>\n/','',$str);
    }

    /**
     * Replace the special characters from the XML string
     * 
     * @param string $string
     * @return string 
     */
    public function replaceXmlSpecialChars($string)
    {
        return preg_replace('/&(?!\w+;)/', '&amp;',$string);
    }

    /**
     *
     * @param int $entityId
     * @return string json data 
     */
    public function getEntityJSON($entityId)
    {
        $entity = $this->loadEntity($entityId);
        $entityEavInfo = array();
        $_data = $entity->getData();
        foreach ($_data as $field=>$value) {
            $entityEavInfo[$field] = $value;
        }
        return json_encode($entityEavInfo);
    }

    /**
     * Get attribute option vlaue set for the specified attribute
     * 
     * @param int $attributeId
     * @return mixed 
     */
    public function getAttributeOptionValues($attributeId)
    {
        return $this->getResource()->getAttributeOptionValues($attributeId);
    }

    /**
     * Add an attribute option value to the set
     * 
     * @param int $attributeId
     * @param mixed $value 
     */
    public function addAttributeOptionValue($attributeId, $value)
    {
        $this->getResource()->addAttributeOptionValue($attributeId, $value);
    }
}
?>
