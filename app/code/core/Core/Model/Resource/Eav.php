<?php
/**
 * class Core_Model_Resource_Eav
 * Resource model for the eav object
 * 
 * @package Core
 * @subpackage Resource
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Resource_Eav extends Core_Model_Resource_Abstract
{
    protected $tbl_eav_attribute = 'eav_attribute';
    protected $tbl_eav_attr_option_value = 'eav_attribute_option_value';
    protected $tbl_eav_entity = 'eav_entity';
    protected $tbl_eav_entity_attribute = 'eav_entity_attribute';
    protected $tbl_eav_entity_type = 'eav_entity_type';
    
    protected $tbl_set_eav = array('datetime'=>'eav_entity_datetime',
                                   'decimal'=>'eav_entity_decimal',
                                   'int'=>'eav_entity_int',
                                   'text'=>'eav_entity_text',
                                   'varchar'=>'eav_entity_varchar');



    /**
     * Get the eav attributes assigned for a specific entity type
     * 
     * @param type $entityTypeId
     * @return array attributes 
     */
    public function getEavAttributes($entityTypeId)
    {
        return $this->_getReadAdapter()->fetchAll("SELECT *
                                                   FROM
                                                    " . $this->tbl_eav_entity_attribute . " AS tbl_entity_attr
                                                    LEFT JOIN " . $this->tbl_eav_attribute . " AS tbl_attr ON tbl_attr.attribute_id = tbl_entity_attr.attribute_id
                                                   WHERE
                                                    tbl_entity_attr.entity_type_id=". $entityTypeId ."
                                                   ORDER BY
                                                    tbl_entity_attr.sort_order, tbl_attr.position, tbl_attr.attribute_id");
    }
    
    /**
     * Load the eav data into the object
     * If attributes is passed (array of attribute_code) the data values will be loaded only for the specified attributes
     *
     * @param Core_Model_Abstract $object
     * @param array $attributes
     * @return Core_Model_Abstract 
     */
    public function loadEavData(Core_Model_Abstract $object, $attributes = array())
    {
        $entityId = $object->getId();
        $entityTypeId = $object->getEntityTypeId();
        if(empty($entityId) || empty($entityTypeId)) { return false; }

        $sql = "SELECT
                        tbl_attr.attribute_code,
                        tbl_attr.attribute_id,
                        tbl_attr.frontend_input,
                        tbl_value.value
                    FROM
                        valueTable  AS tbl_value
                        LEFT JOIN " . $this->tbl_eav_entity_attribute . " AS tbl_entity_attr ON tbl_entity_attr.attribute_id = tbl_value.attribute_id
                        LEFT JOIN " . $this->tbl_eav_attribute . " AS tbl_attr ON tbl_attr.attribute_id = tbl_entity_attr.attribute_id
                    WHERE
                        tbl_value.entity_id = " . $entityId . "
                        AND tbl_entity_attr.entity_type_id = " . $entityTypeId . "";

        //load the data only for the specific attributes if given
        if(!empty ($attributes) && is_array($attributes)) {
            $sql .= " AND tbl_attr.attribute_code IN ('". implode("','", $attributes) . "')";
        }
        
        $eavOptionValues = array();
        foreach($this->tbl_set_eav as $table) {
            $result = $this->_getReadAdapter()->fetchAll(str_replace('valueTable', $table, $sql));
            if(empty($result)) { continue; }

            foreach($result as $row) {
                $object->setData($row['attribute_code'], $row['value']);

                if($row['frontend_input'] == 'select' || $row['frontend_input'] == 'multiselect') {
                    $valueId = intval($row['value']);
                    if(empty ($valueId)) { continue; }
                    $optionValue = $this->_getReadAdapter()->fetchColumn("SELECT value FROM ". $this->tbl_eav_attr_option_value ." WHERE value_id IN (". $valueId .")", 'value');
                    $eavOptionValues[$row['attribute_code']] = implode(',', $optionValue);
                }
            }
        }
        //set the origianl data prior to adding the display data
        $object->setOrigData();
        //set the attribute option values
        foreach($eavOptionValues as $attributeCode=>$value) {
            $object->setData($attributeCode, $value);
        }
        return $object;
    }

    /**
     * Set custom eav Tables
     * 
     * @param array $tablesSet
     * @return Core_Model_Resource_Eav
     */
    public function setEavTables(array $tablesSet)
    {
        foreach(array('static','datetime','decimal','int','text','varchar') as $type) {
            if(empty($tablesSet[$type])) { continue; }
            $this->tbl_set_eav[$type] = $tablesSet[$type];
        }
        return $this;
    }

    /**
     * Check weather the $value is already existing and assigned to the $attribute for another entity object
     *
     * @param int $attributeId
     * @param mixed $value
     * @param string $backendType
     * @param int $entityTypeId
     * @param int $entityId
     * @return arary  
     */
    public function checkUnique($attributeId, $value, $backendType=false, $entityTypeId = false, $entityId = false)
    {
        if(empty($backendType) || empty($entityTypeId)) {
            $attributeInfo = $this->_getReadAdapter()->fetchRow("SELECT entity_type_id, backend_type FROM " . $this->tbl_eav_attribute . " WHERE attribute_id=". $attributeId);
            if(empty($backendType)) $backendType = $attributeInfo['backend_type'];
            if(empty($entityTypeId)) $entityTypeId = $attributeInfo['entity_type_id'];
        }

        $chkSql = "SELECT entity_id FROM ". $this->tbl_set_eav[$backendType] ." WHERE attribute_id = ". $attributeId ." AND value='". $value ."'";
        $chkSql .= !empty($entityId) ? " AND entity_id !=". $entityId : "";

        return $this->_getReadAdapter()->fetchOne($chkSql);
    }

    /**
     * Update the eav data for an entity object
     *
     * @param array $data
     * @param int $entityId
     * @param int $entityTypeId
     * @return Core_Model_Resource_Eav 
     */
    public function updateEavData($data, $entityId, $entityTypeId)
    {
        $this->insertEavData($data, $entityId, $entityTypeId);
        return $this;
    }

    /**
     * Insert eav data for the object
     * The $data array is traversed and attributes are identified with the array keys
     * 
     *
     * @param array $data
     * @param int $entityId
     * @param int $entityTypeId
     * @return Core_Model_Resource_Eav 
     */
    public function insertEavData($data, $entityId, $entityTypeId)
    {
        $attributes = $this->getEavAttributes($entityTypeId);
        if(!$attributes) { return false; }
        
        $insertVals = array();
        foreach($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            $attributeCode = $attribute['attribute_code'];
            $bakcendType = $attribute['backend_type'];
            if(!isset($data[$attributeCode])) continue;

            $value = $this->_prepareValueForSave($data[$attributeCode], $bakcendType);
            $table = $this->tbl_set_eav[$bakcendType];
            $this->_getWriteAdapter()->query("REPLACE INTO ". $table ." (entity_type_id, attribute_id, entity_id, value)
                                                                VALUES(". $entityTypeId ."," . $attributeId . ", ". $entityId . ", " . $value . ")");

        }
        return $this;
    }

    /**
     * Delete the eav data set of the entity object
     *
     * @param int $entityId
     * @param int $entityTypeId
     * @return Core_Model_Resource_Eav 
     */
    public function deleteEavData($entityId, $entityTypeId)
    {
        foreach($this->tbl_set_eav as $table) {
            $this->_getWriteAdapter()->query("DELETE FROM ". $table ." WHERE entity_type_id = ". $entityTypeId ." AND entity_id = ". $entityId);
        }
        return $this;
    }
    
    /**
     * Get the option value set for the attribute
     * 
     * @param int $attributeId
     * @return array option values 
     */
    public function getAttributeOptionValues($attributeId)
    {
        if(empty ($attributeId)) { return false; }
        $condition = "WHERE attr_opt.attribute_id ";
        $condition .= is_array($attributeId) ? "IN (" . implode(",",$attributeId) . ")" : "=".$attributeId;
        $sql = "SELECT
                        attr_opt.attribute_id,
                        attr_opt.value_id,
                        attr_opt.value
                    FROM
                        " . $this->tbl_eav_attr_option_value . " AS attr_opt
                    ". $condition ."
                    ORDER BY
                        attr_opt.attribute_id,
                        attr_opt.sort_order,
                        attr_opt.value";
        $attributeOptionValues = $this->_getReadAdapter()->fetchAll($sql);
        
        if(!$attributeOptionValues) { return false; }

        if(!is_array($attributeId)) {
            return $attributeOptionValues;
        } else {
            $returnValues = array();
            foreach($attributeOptionValues as $optionvalue) {
                $returnValues[$optionvalue['attribute_id']][$optionvalue['value_id']] = $optionvalue['value'];
            }
            return $returnValues;
        }
    }

    /**
     * Load the entity info and entity type info while eidting, 
     * don't use this function to load entities generically
     * 
     * @param int $entityId
     * @return Core_Model_Abstract
     */
    public function getEntityInfo($entityId)
    {
        $sql = "SELECT   *
                FROM
                    " . $this->tbl_eav_entity . " AS entity
                    LEFT JOIN ". $this->tbl_eav_entity_type ." AS entity_type ON entity_type.entity_type_id = entity.entity_type_id
                WHERE  entity.entity_id = " . $entityId . "";
        return $this->_getReadAdapter()->fetchRow($sql);
    }

    /**
     * Get entity model from entity table using the entityTypeId
     * 
     * @param type $entityTypeId
     * @return string model
     */
    public function getEntityModel($entityTypeId)
    {
        return $this->_getReadAdapter()->fetchOne("SELECT entity_model FROM ". $this->tbl_eav_entity_type ." WHERE  entity_type_id = ". $entityTypeId);
    }



    ###################### Attribute Edit Methods ###############
    public function addAttributeOptionValue($attributeId, $optionValue)
    {
        $optionValue = !is_array($optionValue) ? array($optionValue) : $optionValue;
        foreach($optionValue as $value) {
            return $this->_getWriteAdapter()->query("INSERT INTO ". $this->tbl_eav_attr_option_value ." (attribute_id, value) VALUES(". $attributeId .", '". $value ."')");
        }
    }
    
    public function updateAttributeOptionValue($valueId, $value)
    {
        return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_eav_attr_option_value ." SET value= '". $value ."' WHERE value_id=". $valueId ." LIMIT 1");
    }
}
?>
