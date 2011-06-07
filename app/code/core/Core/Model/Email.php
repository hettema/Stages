<?php
/**
 * class Core_Model_Email
 * Object to handle email messages from the system
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Email extends Core_Model_Object
{
    
    protected $_tplVars = array();
    
    protected $_block;

    public function __construct()
    {
        // TODO: move to config
        $this->setFromName('ACE');
        $this->setFromEmail('ace@ace.com');
        $this->setType('text');
    }

    /**
     * Set the template variable
     * 
     * @param string $var
     * @param mixed $value
     * @return Core_Model_Email 
     */
    public function setTemplateVar($var, $value = null)
    {
        if (is_array($var)) {
            foreach ($var as $index=>$value) {
                $this->_tplVars[$index] = $value;
                if($this->_block) {
                    $this->_block->assign($index, $value);
                    $this->_block->setData($var, $value);
                }
            }
        }
        else {
            $this->_tplVars[$var] = $value;
        }
        return $this;
    }

    /**
     *
     * @return array templte variable values  
     */
    public function getTemplateVars()
    {
        return $this->_tplVars;
    }

    /**
     * Prepare the body of the email
     * If the email is configured to be an HTML email the block outbupt is generated 
     * in the same way it is done for loading the layout
     * 
     * @return string 
     */
    public function getBody()
    {
        $body = $this->getData('body');
        if (empty($body) && $this->getTemplate()) {
            $this->_block = App_Main::getModel('core/layout')->createBlock('core/email_template', 'email')
                ->setArea($this->getArea())
                ->setTheme($this->getTheme())
                ->setLocale($this->getLocale())
                ->setTemplate($this->getTemplate());
            foreach ($this->getTemplateVars() as $var=>$value) {
                $this->_block->assign($var, $value);
                $this->_block->setData($var, $value);
            }
            $this->_block->assign('_type', strtolower($this->getType()))
                ->assign('_section', 'body');
            $body = $this->_block->toHtml();
        }
        return $body;
    }

    /**
     * Get the email subject
     * 
     * @return string 
     */
    public function getSubject()
    {
        $subject = $this->getData('subject');
        if (empty($subject) && $this->_block) {
            $this->_block->assign('_section', 'subject');
            $subject = $this->_block->toHtml();
        }
        return $subject;
    }

    /**
     * Get the email locale
     * 
     * @return string locale 
     */
    public function getLocale()
    {
        if($this->hasData('locale')) {
            return $this->getData('locale');
        }

        return App_Main::getWebsite()->getLocale();
    }

    /**
     * Send the email
     * 
     * @param string $_charset
     * @return Core_Model_Email 
     */
    public function send($_charset = 'UTF-8')
    {        
        $mail = new Zend_Mail($_charset);
        if ($this->getReturnPath()) {
            $trans = new Zend_Mail_Transport_Sendmail();
            $trans->parameters = '-f'.$this->getReturnPath();
            $mail->setDefaultTransport($trans);
        }

        if (strtolower($this->getType()) == 'html') {
            $mail->setBodyHtml($this->getBody());
        }
        else {
            $mail->setBodyText($this->getBody());
        }

        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail(), $this->getToName())
            ->setSubject($this->getSubject())
            ->setReturnPath($this->getReturnPath());
        if($this->getCc()) {
            $mail->addCc($this->getCc());
        }
        if($this->getBcc()) {
            $mail->addBcc($this->getBcc());
        }
        $mail->send();

        return $this;
    }
}
?>