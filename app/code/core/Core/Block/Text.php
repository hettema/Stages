<?php
/**
 * class Core_Block_Text
 * 
 * @package Core
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Text extends Core_Block_Abstract
{

    /**
     * Set the text data to be rendered
     * 
     * @param string $text
     * @return Core_Block_Text 
     */
    public function setText($text)
    {
        $this->setData('text', $text);
        return $this;
    }

    /**
     * Get the text data
     * 
     * @return type 
     */
    public function getText()
    {
        return $this->getData('text');
    }

    /**
     * Add text data before or after the current stored text
     * 
     * @param string $text
     * @param bool $before 
     */
    public function addText($text, $before=false)
    {
        if ($before) {
            $this->setText($text.$this->getText());
        } else {
            $this->setText($this->getText().$text);
        }
    }

    /**
     * Render the block text
     * @return type 
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        return $this->getText();
    }

}
?>