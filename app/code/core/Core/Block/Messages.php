<?php
/**
 * class Core_Block_Messages
 * 
 * @package Core
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Messages extends Core_Block_Template
{
    /**
     * Messages collection
     */
    protected $_messages;

    /**
     * Flag which require message text escape
     */
    protected $_escapeMessageFlag = false;

    public function _prepareLayout()
    {
        $this->addMessages(App_Main::getSingleton('core/session')->getMessages(true));
        parent::_prepareLayout();
    }

    /**
     * Set message escape flag
     * 
     * @param bool $flag
     * @return Core_Block_Messages
     */
    public function setEscapeMessageFlag($flag)
    {
        $this->_escapeMessageFlag = $flag;
        return $this;
    }

    /**
     * Add messages to display
     *
     */
    public function addMessages(Core_Model_Message_Collection $messages)
    {
        foreach ($messages->getItems() as $message) {
            $this->getMessageCollection()->add($message);
        }
        return $this;
    }

    /**
     * Retrieve messages collection
     */
    public function getMessageCollection()
    {
        /*if (!($this->_messages instanceof Core_Model_Message_Collection)) {
            $this->_messages = App_Main::getModel('core/message_collection');
        }*/
        return $this->_messages;
    }

    /**
     * Add new message to message collection
     *
     * @param   Core_Model_Message_Abstract $message
     * @return  Core_Block_Messages
     */
    public function addMessage(Core_Model_Message_Abstract $message)
    {
        $this->getMessageCollection()->add($message);
        return $this;
    }

    /**
     * Add new error message
     * 
     * @param string $message
     * @return Core_Block_Messages
     */
    public function addError($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->error($message));
        return $this;
    }

    /**
     * Add new warning message
     * 
     * @param string $message
     * @return Core_Block_Messages
     */
    public function addWarning($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->warning($message));
        return $this;
    }

    /**
     * Add new notice message
     * 
     * @param string $message
     * @return Core_Block_Messages
     */
    public function addNotice($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->notice($message));
        return $this;
    }

    /**
     * Add new success message
     * 
     * @param string $message
     * @return Core_Block_Messages
     */
    public function addSuccess($message)
    {
        $this->addMessage(App_Main::getSingleton('core/message')->success($message));
        return $this;
    }

    /**
     * Get messages array by message type
     */
    public function getMessages($type=null)
    {
        return $this->getMessageCollection()->getItems($type);
    }

    /**
     * Get messages in HTML format
     * 
     * @return string
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
     * Get messages in HTML format grouped by type
     * 
     * @return string
     */
    public function getGroupedHtml()
    {
        return;
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
                    $html.= ($this->_escapeMessageFlag) ? $this->htmlEscape($message->getText()) : $message->getText();
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

    /**
     * Get the message HTML for displaying the stored messages
     * 
     * @return string HTML 
     */
    protected function _toHtml()
    {
        return $this->getGroupedHtml();
    }
}
