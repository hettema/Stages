<?php
/**
 * class Core_Model_Abstract
 * Abstract model object for the module model objects
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Abstract extends Core_Model_Object
{
    protected $_resourceName;

    /**
     * Resource model instance
     *
     * @var Core_Model_Resource_Abstract
     */
    protected $_resource;

    /**
     * Name of the resource collection model
     */
    protected $_resourceCollectionName;

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string || true
     */
    protected $_cacheTag    = false;

    /**
     * Flag which can stop data saving after before save
     * Can be used for next sequence: we check data in _beforeSave, if data are
     * not valid - we can set this flag to false value and save process will be stopped
     *
     * @var bool
     */
    protected $_dataSaveAllowed = true;

    /**
     * Standard model initialization
     *
     * @param string $resourceModel
     * @param string $idFieldName
     * @return Core_Model_Abstract
     */
    protected function _init($resourceModel)
    {
        $this->_setResourceModel($resourceModel);
        return $this;
    }

    /**
     * Set resource names
     *
     * If collection name is ommited, resource name will be used and appended with _collection 
     *
     * @param string $resourceName
     * @param string|null $resourceCollectionName
     */
    protected function _setResourceModel($resourceName, $resourceCollectionName=null)
    {
        $this->_resourceName = $resourceName;
        if (is_null($resourceCollectionName)) {
            $resourceCollectionName = $resourceName.'_collection';
        }
        $this->_resourceCollectionName = $resourceCollectionName;
    }

    /**
     * Get resource instance
     *
     * @return Core_Model_Resource_Abstract
     */
    protected function _getResource()
    {
        if (empty($this->_resourceName)) {
            App_Main::throwException('Resource is not set');
        }

        return App_Main::getResourceSingleton($this->_resourceName);
    }


    /**
     * Get the object model's main table id field
     */
    public function getIdFieldName()
    {
        if (!($fieldName = parent::getIdFieldName())) {
            $fieldName = $this->_getResource()->getIdFieldName();
            $this->setIdFieldName($fieldName);
        }
        return $fieldName;
    }

    /**
     * Get the id of the model object identified by the object id field
     */
    public function getId()
    {
        if ($fieldName = $this->getIdFieldName()) {
            return $this->_getData($fieldName);
        } else {
            return $this->_getData('id');
        }
    }

    /**
     * Set model object id value
     *
     * @param   mixed $id
     * @return  Core_Model_Abstract
     */
    public function setId($id)
    {
        if ($this->getIdFieldName()) {
            $this->setData($this->getIdFieldName(), $id);
        } else {
            $this->setData('id', $id);
        }
        return $this;
    }

    /**
     * Get model resource name
     * 
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }

    /**
     * Get resource collection instance
     *
     * @return Core_Model_Resource_Collection_Abstract
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollectionName)) {
            App_Main::throwException('Model collection resource name is not defined');
        }
        return App_main::getResourceModel($this->_resourceCollectionName, $this->_getResource());
    }

    
    /**
     * Wraper method for getResourceCollection
     *
     * @return Core_Model_Resource_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->getResourceCollection();
    }

    protected function _beforeLoad() { }
    protected function _afterLoad() { }

    /**
     * Load object data from the main table with the id field
     *
     * @param integer $id
     * @param string field
     * @return Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        $this->_beforeLoad();
        $this->_getResource()->load($this, $id, $field);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Save object data into the resource db
     *
     * @return Core_Model_Abstract
     */
    public function save()
    {
        try {
            $this->_beforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->_afterSave();
            }
        }
        catch (Exception $e){
            throw $e;
        }
        return $this;
    }

    /**
     * Process object before save data
     *
     * @return Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        return $this;
    }

    /**
     * Process object after save data
     *
     * @return Core_Model_Abstract
     */
    protected function _afterSave()
    {
        if ($this->_cacheTag) {
            if ($this->_cacheTag === true) {
                $tags = array();
            }
            else {
                $tags = array($this->_cacheTag);
            }
            App_Main::getCacheFactory()->cleanCache($tags);
        }
        return $this;
    }

    /**
     * Delete object from DB
     *
     * @return Core_Model_Abstract
     */
    public function delete()
    {
        try {
            $this->_beforeDelete();
            $this->_getResource()->delete($this);
            $this->_afterDelete();

        }
        catch (Exception $e){
            throw $e;
        }
        return $this;
    }

    /**
     * Process object before delete data
     *
     * @return Core_Model_Abstract
     */
    protected function _beforeDelete()
    {
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        if ($this->_cacheTag) {
            if ($this->_cacheTag === true) {
                $tags = array();
            }
            else {
                $tags = array($this->_cacheTag);
            }
            App_Main::getCacheFactory()->cleanCache($tags);
        }
        return $this;
    }

    /**
     * Get the eav attributes for the entity object model
     * 
     * @return array attributes 
     */
    public function getEavAttributes()
    {
        if(!$this->getEntityTypeId()) { return false; }

        return App_Main::getResourceModel('core/eav')->getEavAttributes($this->getEntityTypeId());
    }

    /**
     * Retrieve model resource
     *
     * @return Core_Model_Resource_Abstract
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Get the entity id from the object data
     * 
     * @return mixed 
     */
    public function getEntityId()
    {
        return $this->_getData('entity_id');
    }

    /**
     * Add a rewrite url parameters to the core_url_rewrite table
     * 
     * @param Core_Model_Abstract $object
     * @param string $idpath
     * @param string $requestPath
     * @param string $targetPath
     * @return Core_Model_Url_Rewrite 
     */
    protected function _addUrlRewrite(Core_Model_Abstract $object, $idpath = null, $requestPath = null, $targetPath = null)
    {
        $rewrite = App_Main::getSingleton('core/url_rewrite')->newUrlRewite($object, $idpath, $requestPath, $targetPath);
        return $rewrite;
    }

    /**
     * Update already existing url rewrite path identified by idPath
     * 
     * @param string $idPath
     * @param string $requestPath
     * @return Core_Model_Abstract 
     */
    protected function _updateUrlRewrite($idPath, $requestPath)
    {
        App_Main::getSingleton('core/url_rewrite')->updateUrlRewite($idPath, $requestPath);
        return $this;
    }
}
?>
