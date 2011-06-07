<?php
/**
 * class Page_Block_Html_Head
 * HTML head block
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html_Head extends Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('page/html/head.phtml');
    }
    
    /**
     * Get the content type and char-encoding of the page
     * 
     * @return string content tyle 
     */
    public function getContentType()
    {
        if (empty($this->_data['content_type'])) {
            $this->_data['content_type'] = $this->getMediaType().'; charset='.$this->getCharset();
        }
        return $this->_data['content_type'];
    }

    /**
     * Get the content type for the current request
     * 
     * @return string content type 
     */
    public function getMediaType()
    {
        if (empty($this->_data['media_type'])) {
            $this->_data['media_type'] = APP_Main::MEDIA_TYPE;
        }
        return $this->_data['media_type'];
    }

    /**
     * Get the charset configured fot the webiste
     * 
     * @return string charset 
     */
    public function getCharset()
    {
        if (empty($this->_data['charset'])) {
            $this->_data['charset'] = APP_Main::CHARSET;
        }
        return $this->_data['charset'];
    }

    /**
     * Set the title of the HTML page
     * title prefix and suffix can be obtained from the main config object
     * 
     * @param string $title
     * @param bool $addPrefix
     * @param bool $addSuffix
     * @return Page_Block_Html_Head 
     */
    public function setTitle($title, $addPrefix = true, $addSuffix = true)
    {
        $titleStr = '';
        if($addPrefix) {
            $titleStr .= App_Main::getWebsite()->getConfig('website-title-prefix') ? App_Main::getWebsite()->getConfig('website-title-prefix') : APP_Main::TITLE_PREFIX;
        }
        $titleStr .= $title;
        if($addPrefix) {
            $titleStr .= App_Main::getWebsite()->getConfig('website-title-prefix') ? App_Main::getWebsite()->getConfig('website-title-suffix') : APP_Main::TITLE_SUFFIX;
        }
        $this->setData('title', $titleStr);
        return $this;
    }

    /**
     * Get the page title 
     * 
     * @return string page title 
     */
    public function getTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_data['title'] = App_Main::getDefaultTitle();
        }
        return htmlspecialchars(html_entity_decode($this->_data['title'], ENT_QUOTES, 'UTF-8'));
    }

    /**
     *
     * @return string meta description 
     */
    public function getDescription()
    {
        if (empty($this->_data['description'])) {
            $this->_data['description'] = App_Main::getDefaultDescription();
        }
        return $this->_data['description'];
    }

    /**
     *
     * @return string get meta keywords 
     */
    public function getKeywords()
    {
        if (empty($this->_data['keywords'])) {
            $this->_data['keywords'] = App_Main::getDefaultKeywords();
        }
        return $this->_data['keywords'];
    }

    /**
     *
     * @return string robot configuration 
     */
    public function getRobots()
    {
        if (empty($this->_data['robots'])) {
            $this->_data['robots'] = App_Main::getDefaultRobots();
        }
        return $this->_data['robots'];
    }
}
?>