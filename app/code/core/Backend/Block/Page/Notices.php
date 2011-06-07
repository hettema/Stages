<?php
/**
 * class Backend_Block_Page_Notices
 * Block to show the notice page if there any configuration errors
 * 
 * @package Backend
 * @subpackage Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Page_Notices extends Backend_Block_Template
{
    /**
     * Check if noscript notice should be displayed
     */
    public function displayNoscriptNotice()
    {
        return true;
    }
    
}