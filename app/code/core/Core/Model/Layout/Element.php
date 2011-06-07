<?php
/**
 * class Core_Model_Layout_Element
 * 
 * @package Core
 * @subpackage Layout
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Layout_Element extends SimpleXMLElement
{
    /**
     * Get the block name from the block element
     * 
     * @return string block name 
     */
    public function getBlockName()
    {
        $tagName = (string)$this->getName();
        if ('block'!==$tagName || empty($this['name'])) {
            return false;
        }
        return (string)$this['name'];
    }
}