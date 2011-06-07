<?php
/**
 * class Backend_Block_Text_List
 * 
 * @package Backend
 * @subpackage Text
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Backend_Block_Text_List  extends Core_Block_Text_List
{
    /**
     * Override the parent function with the backend url model class
     * @return type 
     */
    protected function _getUrlModelClass()
    {
        return 'backend/url';
    }
}
?>