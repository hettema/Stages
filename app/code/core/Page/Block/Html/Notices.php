<?php
/**
 * class Page_Block_Html_Notices
 * Block to show the notice page if there any configuration errors
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html_Notices extends Core_Block_Template
{
    /**
     * Check if the noscript notice is to be displayed
     */
    public function displayNoscriptNotice()
    {
        return true;
    }
}