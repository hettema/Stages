<?php
/**
 * class Backend_Block_Page_Footer
 * 
 * @package Backend
 * @subpackage Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Page_Footer extends Backend_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('page/html/footer.phtml');
    }    
}
?>