<?php
/**
 * class Core_Model_Resource_Abstract
 * Abstract class for Resource model handling the database operations
 * 
 * @package Core
 * @subpackage Resource
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
abstract class Core_Model_Resource_Abstract  extends Core_Model_Object
{
    /**
     * DB Connections cache
     */
    protected $_connections = array();

    /**
     * Main table name
     */
    protected $_mainTable;

    /**
     * Main table primary id field name
     */
    protected $_idFieldName;

    /**
     * Primery key auto increment flag
     */
    protected $_isPkAutoIncrement = true;

    /**
     * @var Mysql_Query
     */
    protected $_query;

    public function __construct()
    {
        $this->_construct();
    }

    /**
     * Set the main table and id field name
     * 
     * @param string $mainTable
     * @param string $idFieldName 
     */
    protected function _init($mainTable, $idFieldName)
    {
        $this->_setMainTable($mainTable, $idFieldName);
    }
    
    /**
     * Set the main table and id field name
     * 
     * @param string $mainTable
     * @param string $idFieldName
     * @return Core_Model_Resource_Abstract 
     */
    protected function _setMainTable($mainTable, $idFieldName=null)
    {
        $this->_mainTable = $mainTable;
        if (is_null($idFieldName)) {
            $idFieldName = $mainTable.'_id';
        }
        $this->_idFieldName = $idFieldName;

        return $this;
    }

    /**
     *
     * @return string id field name 
     */
    public function getIdFieldName()
    {
        if (empty($this->_idFieldName)) {
            App_Main::throwException('Empty identifier field name');
        }
        return $this->_idFieldName;
    }

    /**
     *
     * @return string main talbe name 
     */
    public function getMainTable()
    {
        if (empty($this->_mainTable)) {
            App_Main::throwException('Empty main table name');
        }
        return $this->_mainTable;
    }

    /**
     * Get the connection from the connection cache
     *
     * @param string $connectionName
     * @return Main_Mysql connection 
     */
    protected function _getConnection($connectionName)
    {
        if (isset($this->_connections[$connectionName])) {
            return $this->_connections[$connectionName];
        } else {
            $this->_connections[$connectionName] = App_Main::getDbAdapter();
        }
        return $this->_connections[$connectionName];
    }

    /**
     * Get the read DB adapter
     *
     * @return Main_Mysql connection  
     */
    protected function _getReadAdapter()
    {
        return $this->_getConnection('read');
    }

    /**
     * Get the write DB adapter
     *
     * @return Main_Mysql connection  
     */
    protected function _getWriteAdapter()
    {
        return $this->_getConnection('write');
    }

     /**
     * Load an object model data from db
     *
     * @param   Core_Model_Abstract $object
     * @param   mixed $value
     * @param   string $field field to load by (defaults to model id)
     * @return  Core_Model_Resource_Abstract
     */
    public function load(Core_Model_Abstract $object, $value, $field=null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();
        if (!is_null($value)) {
            $data = $this->_getReadAdapter()->fetchRow("SELECT * FROM " . $this->getMainTable() . " WHERE ". $field ."='". $value ."'");

            if ($data) {
                $object->setData($data);
            }
        }
        if($object->getId()) {
        $this->_afterLoad($object);
        }

        return $this;
    }
    
    /**
     * Process object data after loading
     * 
     * @param Core_Model_Abstract $object 
     */
    protected function _afterLoad(Core_Model_Abstract $object) { }

    /**
     * Process object before loading
     *
     * @param Core_Model_Abstract $object 
     */
    protected function _beforeSave(Core_Model_Abstract $object) { }
    
    /**
     * Check unique data fields before saving an object data
     *
     * @param Core_Model_Abstract $object 
     */
    protected function _checkUnique(Core_Model_Abstract $object) {}
    
    /**
     * Process object before saving
     *
     * @param Core_Model_Abstract $object 
     */
    protected function _afterSave(Core_Model_Abstract $object) {}

    /**
     * Save the object data
     *
     * @param Core_Model_Abstract $object
     * @return Core_Model_Resource_Abstract 
     */
    public function save(Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_beforeSave($object);
        $this->_checkUnique($object);

        if (!is_null($object->getId())) {
            // Not auto increment primary key support
            if ($this->_isPkAutoIncrement) {
                $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $this->getIdFieldName());
            } else {
                if ($this->_getWriteAdapter()->fetchOne("SELECT * FROM ". $this->getMainTable() ." WHERE ". $this->getIdFieldName()."=". $object->getId()) !== false) {
                    $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $this->getIdFieldName());
                } else {
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object, true));
                }
            }
        } else {
            $id = $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object, true), $this->getIdFieldName());
            $object->setId($id);
        }

        $this->_afterSave($object);

        return $this;
    }

    /**
     * Process object before deleting
     *
     * @param Core_Model_Abstract $object 
     */
    protected function _beforeDelete(Core_Model_Abstract $object) {}
    
    /**
     * Process object after deleting
     *
     * @param Core_Model_Abstract $object 
     */
    protected function _afterDelete(Core_Model_Abstract $object) {}
    
    /**
     * Delete the object data from database
     *
     * @param Core_Model_Object $object
     */
    public function delete(Core_Model_Abstract $object)
    {
        $this->_beforeDelete($object);
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(), $this->getIdFieldName() .'='. $object->getId()
        );
        $this->_afterDelete($object);
        return $this;
    }

    /**
     * Prepare the object data for saving to the database
     * If $table is null, the main table is used and the fields are identified 
     *
     * @param Core_Model_Abstract $object
     * @param bool $graceful
     * @param string $table
     * @return type 
     */
    protected function _prepareDataForSave(Core_Model_Abstract $object, $graceful = false, $table = false)
    {
        $data = array();
        $table = !$table ? $this->getMainTable() : $table;
        $fields = $this->_getWriteAdapter()->describeTable($table);
        foreach ($fields as $field=>$fieldInfo) {
            
            if(!$object->hasData($field) && $fieldInfo['Null'] == 'NO') {
                if(isset($fieldInfo['Default'])) { continue; }
                if(isset($fieldInfo['Key']) && $fieldInfo['Key'] == 'PRI') { continue; } //@# ToDO add the check to auto increment here if needed
            }

            if($object->hasData($field)) {
                $data[$field] = $this->_prepareValueForSave($object->getData($field), $fieldInfo['data_type']);
            } else if ($fieldInfo['Null'] == 'NO' && !isset($fieldInfo['Default']) && $graceful) { //set empty string for those field which does not have default value and is nullable
                $data[$field] = $this->_prepareValueForSave('');
            } else {
                continue;
            }
        }
        return $data;
    }

    /**
     * Prepare value for saving
     *
     * @param mixed $value
     * @param string $type
     * - int
     * - decimal
     * - text
     * @return type 
     */
    protected function _prepareValueForSave($value, $type='text')
    {
        switch($type)
        {
            case 'int': //returns '0' for an empty $value
                return (strlen($value) > 10) ? $value : intval($value); #@ToDO make it possible to analyse the data type and return proper int value
            break;
            case 'decimal':
                return floatval($value);
            break;
            default:
                return "'". mysql_real_escape_string($value) ."'";
            break;
        }
    }

    /**
     *
     * @return Main_Mysql_Query object 
     */
    public function _getQuery()
    {
        if(!$this->_query) {
            $this->_query = new Main_Mysql_Query();
        }
        return $this->_query;
    }

}
