<?php
/**
 * class Backend_Block_Page_Head
 * 
 * @package Backend
 * @subpackage Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Page_Head extends Page_Block_Html_Head
{
    protected function _getUrlModelClass()
    {
        return 'backend/url';
    }
}
?>