<?php
/**
 * class Stages_Block_Login
 * 
 * @package Stages
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Block_Login extends Stages_Block_Abstract
{
    /**
     * Get the login box header message
     * @return string 
     */
    public function getHeaderMessage()
    {
        return !$this->getData('header_message') ? 'Welcome to Stages' : $this->getData('header_message');
    }

    public function showSignupForm()
    {
        return true;
    }

    /**
     * Get the signup HTML for the signup block
     * using template file stages/signup.phtml
     * @return string HTML 
     */
    public function getSignupHtml()
    {
        return $this->getLayout()->createBlock('core/template', 'signup-block', array('template'=>'stages/signup.phtml'))->toHtml();
    }
}