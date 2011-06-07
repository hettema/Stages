<?php
/**
 * class Core_Block_Text_List
 * When rendered traverese through all the child blocks defined and get its html view
 * 
 * @package Core
 * @subpackage Text
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Text_List extends Core_Block_Text
{
    protected function _toHtml()
    {
        $this->setText('');
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                App_Main::throwException('Invalid block: %s', $name);
            }
            $this->addText($block->toHtml());
        }
        return parent::_toHtml();
    }
}
?>