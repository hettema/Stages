<?php
/**
 * class Core_Model_Resource_Config
 * Resource model for the config object
 * 
 * @package Core
 * @subpackage Resource
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Resource_Config extends Core_Model_Resource_Abstract
{

    protected $tbl_config_data = 'core_config_data';

    protected function _construct()
    {
        $this->_init($this->tbl_config_data, 'config_id');
    }

    /**
     * Load the confgi data into the passed $object
     *
     * @param Core_Model_Abstract $object
     * @param string $scope
     * @param int $scopeId
     * @return Core_Model_Abstract 
     */
    public function loadConfigData(Core_Model_Abstract $object, $scope ='default', $scopeId = 0)
    {
        $results = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_config_data ." WHERE scope='". $scope ."' AND scope_id=". $scopeId ."
                                                       GROUP BY path ORDER BY scope DESC");
        if(!$results) { return $object; }
        
        foreach($results as $result) {
            $object->setData($result['path'], $result['value']);
        }
        return $object;
    }

    /**
     * Save config value
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Core_Model_Resource_Config
     */
    public function saveConfig($path, $value, $scope, $scopeId)
    {
        $newData = array(
            'scope'     => $this->_prepareValueForSave($scope),
            'scope_id'  => $this->_prepareValueForSave($scopeId, 'int'),
            'path'      => $this->_prepareValueForSave($path),
            'value'     => $this->_prepareValueForSave($value)
        );
        $configId = $this->_getReadAdapter()->fetchOne("SELECT ". $this->getIdFieldName() ." FROM ". $this->getMainTable() ." WHERE path='". $path ."' AND scope='". $scope ."' AND scope_id='". $scopeId ."'");

        if($configId) {
            $newData[$this->getIdFieldName()] = $configId;
            $this->_getWriteAdapter()->update($this->getMainTable(), $newData, $this->getIdFieldName());
        } else {
            $this->_getWriteAdapter()->insert($this->getMainTable(), $newData);            
        }
        
        return $this;
    }

    /**
     * Delete config value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return Core_Model_Resource_Config
     */
    public function deleteConfig($path, $scope, $scopeId)
    {
        $this->_getWriteAdapter()->query("DELETE FROM ". $this->getMainTable() ." WHERE path='". $path ."' AND scope='". $scope ."' AND scope_id='". $scopeId ."'");
        return $this;
    }

    /**
     * Get the scope id from the config main table
     *
     * @param string $scope
     * @param string $path
     * @param mixed $value
     * @return int scope id
     */
    public function getScopeIdFromPathValue($scope ='default', $path, $value)
    {
        return $this->_getReadAdapter()->fetchOne("SELECT scope_id FROM ". $this->getMainTable() ." WHERE scope='". $scope ."' AND path='". $path ."' AND value=". $this->_prepareValueForSave($value));
    }
}
?>
