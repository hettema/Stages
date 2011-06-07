<?php
/**
 * class Core_Block_Email_Template
 * 
 * @package Core
 * @subpackage Email
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Email_Template extends Core_Block_Template
{
    protected $_defaltLocale = 'en_US';

    /**
     * Set the template file
     * 
     * @param type $template
     * @return Core_Block_Email_Template 
     */
    public function setTemplate($template)
    {
        $fileName = App_Main::getBaseDir('locale') .DS.'email'.DS. $this->getLocale() .DS. $template;

        if(file_exists($fileName)) {
            $this->setData('template', $fileName);
        } else { //no need to check file existance here as it will be checked durinng ->fetchview
            $fileName = App_Main::getBaseDir('locale') .DS.'email'.DS. $this->_defaltLocale .DS. $template;
            $this->setData('template', $fileName);
        }
        return $this;
    }

    /**
     * Render the email body 
     * 
     * @return type 
     */
    public function renderView()
    {
        return $this->fetchView($this->getTemplate());
    }

    /**
     * Get the skin files (images) url for the selected theme and area
     *
     * @param string $file
     * @param array $params
     * @return string 
     */
    public function  getSkinUrl($file = null, array $params = array())
    {
        if(empty ($params['_area'])) {
            $params['_area'] = $this->getArea();
        }
        if(empty ($params['_theme'])) {
            $params['_theme'] = $this->getTheme();
        }
        return parent::getSkinUrl($file, $params);
    }

    /**
     * Get the configured are
     * 
     * @return type 
     */
    public function getArea()
    {
        if($this->getData('area')) {
            return $this->getData('area');
        }
        return Core_Model_Design::DEFAULT_AREA;
    }

    /**
     * Get the email theme
     * 
     * @return type 
     */
    public function getTheme()
    {
        if($this->getData('theme')) {
            return $this->getData('theme');
        }
        return Core_Model_Design::DEFAULT_THEME;
    }
}
?>
