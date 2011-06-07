<?php
/**
 * class Core_Exception
 * 
 * @package     Core
 * @category    Exception
 * @copyright   Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Exception extends Exception
{
    protected $_messages = array();

    /**
     * Add a message to the object instance
     * @param Core_Model_Message_Abstract $message
     * @return Core_Exception 
     */
    public function addMessage(Core_Model_Message_Abstract $message)
    {
        if (!isset($this->_messages[$message->getType()])) {
            $this->_messages[$message->getType()] = array();
        }
        $this->_messages[$message->getType()][] = $message;
        return $this;
    }   
    
    /**
     *
     * @param string $type
     * @return Core_Model_Message_Abstract  
     */
    public function getMessages($type='')
    {
        if ('' == $type) {
            $arrRes = array();
            foreach ($this->_messages as $messageType => $messages) {
                $arrRes = array_merge($arrRes, $messages);
            }
            return $arrRes;
        }
        return isset($this->_messages[$type]) ? $this->_messages[$type] : array();
    }
}
?>