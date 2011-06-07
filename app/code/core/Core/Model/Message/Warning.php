<?php
/**
 * class Core_Model_Message_Warning
 * 
 * @package Core
 * @subpackage Message
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Message_Warning extends Core_Model_Message_Abstract
{
    public function __construct($code)
    {
        parent::__construct(Core_Model_Message::WARNING, $code);
    }
}