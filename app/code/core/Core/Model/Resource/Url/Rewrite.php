<?php
/**
 * class Core_Model_Resource_Url_Rewrite
 * Resource model for url rewrite model
 * 
 * @package Core
 * @subpackage Url
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Resource_Url_Rewrite extends Core_Model_Resource_Abstract
{
    protected $tbl_url_rewrite = 'core_url_rewrite';
    protected $_tagTable;

    protected function _construct()
    {
        $this->_init($this->tbl_url_rewrite, 'url_rewrite_id');
    }

    /**
     * Update rewrite info
     *
     * @param string $idPath
     * @param string $requestpath
     * @return bool query result 
     */
    public function updateUrlRewrite($idPath, $requestpath)
    {
        return $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_url_rewrite ." SET request_path=". $this->_prepareValueForSave($requestpath) ." WHERE id_path =". $this->_prepareValueForSave($idPath));
    }

    /**
     * Remove the rewrite data by the $idPath
     *
     * @param string $idPath
     * @return bool query result 
     */
    public function removeUrlRewrite($idPath)
    {
        return $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_url_rewrite ." WHERE id_path =". $this->_prepareValueForSave($idPath) ." LIMIT 1");
    }
}
?>