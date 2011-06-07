<?php
/**
 * class Core_Block_Session_Messages
 * 
 * @package Core
 * @subpackage Session
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Session_Messages extends Core_Block_Template
{

    protected $_messages = array();
    /**
     * Get messages array by message type
     */
    public function getMessages($type=null)
    {
        if(empty($this->_messages)) {
            $this->_messages = App_Main::getSession()->getMessages(true);
        }
        if($this->_messages->getItems($type)) {
            return $this->_messages->getItems($type);
        }
        return false;
    }

    /**
     * Get messages in HTML format
     */
    public function getHtml($type=null)
    {
        return;
        $html = '<ul id="admin_messages">';
        foreach ($this->getMessages($type) as $message) {
            $html.= '<li class="'.$message->getType().'-msg">'
                . ($this->_escapeMessageFlag) ? $this->htmlEscape($message->getText()) : $message->getText()
                . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Retrieve messages in HTML format grouped by type
     */
    public function getGroupedHtml()
    {
        $types = array(
            Core_Model_Message::ERROR,
            Core_Model_Message::WARNING,
            Core_Model_Message::NOTICE,
            Core_Model_Message::SUCCESS
        );
        $html = '';
        foreach ($types as $type) {
            if ( $messages = $this->getMessages($type) ) {
                if ( !$html ) {
                    $html .= '<ul class="messages">';
                }
                $html .= '<li class="' . $type . '-msg">';
                $html .= '<ul>';

                foreach ( $messages as $message ) {
                    $html.= '<li>';
                    $html.= $message->getText();
                    $html.= '</li>';
                }
                $html .= '</ul>';
                $html .= '</li>';
            }
        }
        if ( $html) {
            $html .= '</ul>';
        }
        return $html;
    }

    protected function _toHtml()
    {
        return $this->getGroupedHtml();
    }
}
?>
