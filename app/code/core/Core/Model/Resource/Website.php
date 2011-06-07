<?php
/**
 * class Core_Model_Resource_Website
 * Resource model for the website object
 * 
 * @package Core
 * @subpackage Resource
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Resource_Website extends Core_Model_Resource_Abstract
{

    /**
     *
     * @var string main table 
     */
    protected $tbl_website = 'core_website';

    protected function _construct()
    {
        $this->_init($this->tbl_website, 'website_id');
    }

    /**
     *
     * @return array of object Core_Model_Website
     */
    public function getWebsites()
    {
        $results = $this->_getReadAdapter()->fetchAll("SELECT * FROM ". $this->tbl_website);
        $websites = array();
        if(!$results) { return $websites; }

        foreach($results as $result) {
            $websites[] = App_Main::getModel('core/website', $result);
        }
        return $websites;
    }

    /**
     * Validate website code to find existing
     * 
     * @param string $code
     * @return bool 
     */
    public function validateWebsiteCode($code)
    {
        $result = $this->_getReadAdapter()->fetchOne("SELECT website_id FROM ". $this->tbl_website ." WHERE code =". $this->_prepareValueForSave($code));
        return empty ($result);
    }

    /**
     * Load the default website to the $website object
     * 
     * @param Core_Model_Website $website
     * @return type 
     */
    public function loadDefaultWebsite(Core_Model_Website $website)
    {
        $result = $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_website ." WHERE is_default = 1 LIMIT 1");
        return $website->setData($result);
    }

    /**
     * Get the default website
     *
     * @return Core_Model_Website 
     */
    public function getDefaultWebsite()
    {
        $result = $this->_getReadAdapter()->fetchRow("SELECT * FROM ". $this->tbl_website ." WHERE is_default = 1 LIMIT 1");
        return App_Main::getModel('core/website', $result);
    }
}
?>
