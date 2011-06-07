<?php
/**
 * class Core_Model_Message_Collection
 * 
 * @package Core
 * @subpackage Message
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Message_Collection
{
    /**
     * All messages by type array
     *
     * @var array
     */
    protected $_messages = array();
    
    protected $_lastAddedMessage;

    /**
     * Add new message to collection
     *
     * @param   Core_Model_Message_Abstract $message
     * @return  Core_Model_Message_Collection
     */
    public function add(Core_Model_Message_Abstract $message)
    {
        return $this->addMessage($message);
    }

    /**
     * Add new message to collection
     *
     * @param   Core_Model_Message_Abstract $message
     * @return  Core_Model_Message_Collection
     */
    public function addMessage(Core_Model_Message_Abstract $message)
    {
        if (!isset($this->_messages[$message->getType()])) {
            $this->_messages[$message->getType()] = array();
        }
        $this->_messages[$message->getType()][] = $message;
        $this->_lastAddedMessage = $message;
        return $this;
    }

    /**
     * Clear all messages except sticky
     *
     * @return Core_Model_Message_Collection
     */
    public function clear()
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if (!$message->getIsSticky()) {
                    unset($this->_messages[$type][$id]);
                }
            }
            if (empty($this->_messages[$type])) {
                unset($this->_messages[$type]);
            }
        }
        return $this;
    }

    /**
     * Get last added message if any
     *
     * @return Core_Model_Message_Abstract|null
     */
    public function getLastAddedMessage()
    {
        return $this->_lastAddedMessage;
    }

    /**
     * Get first even message by identifier
     *
     * @param string $identifier
     * @return Core_Model_Message_Abstract|null
     */
    public function getMessageByIdentifier($identifier)
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if ($identifier === $message->getIdentifier()) {
                    return $message;
                }
            }
        }
    }

    /**
     * Delete a message using the identifier
     *
     * @param int $identifier 
     */
    public function deleteMessageByIdentifier($identifier)
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if ($identifier === $message->getIdentifier()) {
                    unset($this->_messages[$type][$id]);
                }
                if (empty($this->_messages[$type])) {
                    unset($this->_messages[$type]);
                }
            }
        }
    }

    /**
     * Get messages collection items
     *
     * @param   string $type
     * @return  array
     */
    public function getItems($type=null)
    {
        if ($type) {
            return isset($this->_messages[$type]) ? $this->_messages[$type] : array();
        }

        $arrRes = array();
        foreach ($this->_messages as $messageType => $messages) {
            $arrRes = array_merge($arrRes, $messages);
        }

        return $arrRes;
    }

    /**
     * Get all messages by type
     *
     * @param   string $type
     * @return  array
     */
    public function getItemsByType($type)
    {
        return isset($this->_messages[$type]) ? $this->_messages[$type] : array();
    }

    /**
     * Get all error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getItemsByType(Core_Model_Message::ERROR);
    }

    /**
     * Get message string from all the message items
     *
     * @return string 
     */
    public function toString()
    {
        $out = '';
        $arrItems = $this->getItems();
        foreach ($arrItems as $item) {
            $out.= $item->toString();
        }

        return $out;
    }

    /**
     * Get messages count
     *
     * @return int
     */
    public function count($type=null)
    {
        if ($type) {
            if (isset($this->_messages[$type])) {
                return count($this->_messages[$type]);
            }
            return 0;
        }
        return count($this->_messages);
    }
}