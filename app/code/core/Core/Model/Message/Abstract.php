<?php
/**
 * class Core_Model_Message_Abstract
 * Abstract class for the message object
 * 
 * @package Core
 * @subpackage Message
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
abstract class Core_Model_Message_Abstract
{
    protected $_type;
    protected $_message;
    protected $_class;
    protected $_method;
    protected $_identifier;
    protected $_isSticky = false;

    public function __construct($type, $message='')
    {
        $this->_type = $type;
        $this->_message = $message;
    }

    /**
     *
     * @return string message string 
     */
    public function getText()
    {
        return $this->_message;
    }

    /**
     *
     * @return string message type 
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     *
     * @param string $class 
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     *
     * @param string $method 
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Get a string representation of the message type: message
     *
     * @return string 
     */
    public function toString()
    {
        $out = $this->getType() .': '. $this->getText();
        return $out;
    }

    /**
     * Set an identifier for the message object
     * 
     * @param mixed $id
     * @return Core_Model_Message_Abstract 
     */
    public function setIdentifier($id)
    {
        $this->_identifier = $id;
        return $this;
    }

    /**
     * Get the identifier set for the object
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Set the message as a sticky message, this will retain the message even if clear message is called
     * 
     * @param bool $isSticky
     * @return Core_Model_Message_Abstract
     */
    public function setIsSticky($isSticky = true)
    {
        $this->_isSticky = $isSticky;
        return $this;
    }

    /**
     * Get the message sticky boolean attribute
     *
     * @return bool  
     */
    public function getIsSticky()
    {
        return $this->_isSticky;
    }
}
?>
