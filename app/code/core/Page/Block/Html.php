<?php
/**
 * class Page_Block_Html
 * HTML page view block
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html extends Core_Block_Template
{
    protected $_urls = array();
    protected $_title = '';

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->_beforeCacheUrl();
    }
  
    /**
     * Get the website language code
     * 
     * @return string lang_code
     */
    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', App_Main::getWebsite()->getConfig('web-default-language'));
        }
        return $this->getData('lang');
    }
    
    /**
     * Set the title of the HTML page
     * 
     * @param string $title
     * @return Page_Block_Html 
     */
    public function setHeaderTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * Get the HTML page title
     * 
     * @return string page title 
     */
    public function getHeaderTitle()
    {
        return $this->_title;
    }

    /**
     * Set the CSS class to HTML body tag
     * 
     * @return Page_Block_Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9\s]+#', '-', strtolower($className));
        $this->setBodyClass($className);
        return $this;
    }

    /**
     * Retrive the body element css class
     * @return string body element class 
     */
    public function getBodyClass()
    {
        if(!$this->_getData('body_class')) {
            $action = App_Main::getRequest()->getActionName();
            $controller = App_Main::getRequest()->getControllerName();
            if ($action && $controller) {
                $this->addBodyClass($controller .'_'. $action);
            }
        }
        
        return $this->_getData('body_class');
    }

    /**
     * Set the theme for the layout
     * @param string $theme
     * @return Page_Block_Html 
     */
    public function setTheme($theme)
    {
        App_Main::getDesign()->setTheme($theme);
        return $this;
    }
    
    /**
     * Processing block html after rendering
     * @param string html
     * @return string html
     */
    protected function _afterToHtml($html)
    {
        return $this->_afterCacheUrl($html);
    }
}
?>