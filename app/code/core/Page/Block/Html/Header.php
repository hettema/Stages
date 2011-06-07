<?php
/**
 * class Page_Block_Html_Header
 * Block contains the log and the top links
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html_Header extends Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('page/html/header.phtml');
    }

    /**
     * Set the log properties so that the content can be switchable
     * 
     * @param string $logo_src
     * @param string $logo_alt
     * @return Page_Block_Html_Header 
     */
    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    /**
     * Get the logo image url
     * 
     * @return string url 
     */
    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = 'images/logo.png';
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }

    /**
     *
     * @return string log image alt text 
     */
    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = App_Main::LOGO_ALT;
        }
        return $this->_data['logo_alt'];
    }

    /**
     * Get the welcome message, can be used when a user logs in for the first time
     * 
     * @return string welcome message 
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            /*if (App_Main::getSession()->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->htmlEscape(App_Main::getSingleton('user/session')->getUser()->getName()));
            }*/
            $this->_data['welcome'] = 'Welcome Guest';
        }
        return $this->_data['welcome'];
    }
}