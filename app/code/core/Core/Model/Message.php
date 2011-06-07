<?php
/**
 * class Core_Model_Message
 * Object to store the system wide messages (viz. success, error, notice, warning)
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Message
{
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const SUCCESS   = 'success';
    
    /**
     * Add a new  message object to the message storage besed on the type
     * 
     * @param string $message
     * @param string $type
     * @param string $class
     * @param string $method
     * @return Core_Model_Message_Abstract 
     */
    protected function _factory($message, $type, $class='', $method='')
    {
        switch (strtolower($type)) {
            case self::ERROR :
                $megObj = new Core_Model_Message_Error($message);
                break;
            case self::WARNING :
                $megObj = new Core_Model_Message_Warning($message);
                break;
            case self::SUCCESS :
                $megObj = new Core_Model_Message_Success($message);
                break;
            default:
                $megObj = new Core_Model_Message_Notice($message);
                break;
        }
        $megObj->setClass($class);
        $megObj->setMethod($method);
        
        return $megObj;
    }
    
    /**
     * Add an error message to the message stack
     * 
     * @param string $message
     * @param string $class object triggered the call
     * @param string $method object method triggered the call
     * @return Core_Model_Message_Abstract 
     */
    public function error($message, $class='', $method='')
    {
        return $this->_factory($message, self::ERROR, $class, $method);
    }
    
    /**
     * Add a warning message to the message stack
     * 
     * @param string $message
     * @param string $class object triggered the call
     * @param string $method object method triggered the call
     * @return Core_Model_Message_Abstract 
     */
    public function warning($message, $class='', $method='')
    {
        return $this->_factory($message, self::WARNING, $class, $method);
    }

    
    /**
     * Add a notice message to the message stack
     * 
     * @param string $message
     * @param string $class object triggered the call
     * @param string $method object method triggered the call
     * @return Core_Model_Message_Abstract 
     */
    public function notice($message, $class='', $method='')
    {
        return $this->_factory($message, self::NOTICE, $class, $method);
    }

    
    /**
     * Add a success message to the message stack
     * 
     * @param string $message
     * @param string $class object triggered the call
     * @param string $method object method triggered the call
     * @return Core_Model_Message_Abstract 
     */
    public function success($message, $class='', $method='')
    {
        return $this->_factory($message, self::SUCCESS, $class, $method);
    }
}
?>