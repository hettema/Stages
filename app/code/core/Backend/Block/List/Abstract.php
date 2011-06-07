<?php
/**
 * class Backend_Block_List_Abstract
 * Abstract class for any paginated list
 * 
 * @package Backend
 * @subpackage List
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_List_Abstract extends Backend_Block_Template
{
    protected $_collection = array();
    protected $_defaultToolbarBlock = 'backend/list_toolbar';

    public function __construct()
    {
        
    }

    /**
     * Load the toolbar block and result collection before rendering the list
     * 
     * @return type 
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getResultCollection();

        // set collection to tollbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        
        return parent::_beforeToHtml();
    }

    /**
     * Get the toolbar block
     *
     * @return Catalog_Block_List_Toolbar
     */
    public function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }

    /**
     * Get the list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }    
}
?>