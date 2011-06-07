<?php
/**
 * class Page_Block_Html_Footer
 * HTML footer block
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html_Footer extends Core_Block_Template
{
    /**
     * Set the footer template file on initialization
     * Cache parameters can be set here
     */
    protected function _construct()
    {
        $this->setTemplate('page/html/footer.phtml');
        /*$this->addData(array(
            'cache_lifetime'=> false,
            'cache_tags'    => array('PAGE_FOOTER')
        ));*/
    }

    /**
     * Retrieve Key for caching block HTML
     */
    public function getCacheKey()
    {
        return 'PAGE_FOOTER_' . $this->getName();
    }
    
    /**
     * Get HTML view for a child block sorted
     * If name is not specified, this will return the HTML of all the child blocks
     * 
     * @param string $name
     * @param bool $useCache
     * @param bool $sorted
     * @return type 
     */
    public function getChildHtml($name='', $useCache=true, $sorted=true)
    {
        return parent::getChildHtml($name, $useCache, $sorted);
    }
    
}
?>