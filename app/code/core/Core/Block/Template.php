<?php
/**
 * class Core_Block_Template
 * 
 * @package Core
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Block_Template extends Core_Block_Abstract
{

    /**
     * View scripts directory
     */
    protected $_viewDir = '';

    /**
     * Assigned variables for view
     */
    protected $_viewVars = array();

    protected $_baseUrl;

    protected $_jsUrl;

    protected static $_showTemplateHints;
    protected static $_showTemplateHintsBlocks;

    public function getTemplate()
    {
        return $this->_getData('template');
    }

    public function getArea()
    {
        return $this->_getData('area');
    }

    /**
     * Assign variable
     */
    public function assign($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        }
        else {
            $this->_viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Set template location dire
     */
    public function setScriptPath($dir)
    {
        $this->_viewDir = $dir;
        return $this;
    }

    public function getDirectOutput()
    {
        if ($this->getLayout()) {
            return $this->getLayout()->getDirectOutput();
        }
        return false;
    }

    /**
     * Retrieve block view from file (template)
     */
    public function fetchView($fileName)
    {        
        extract ($this->_viewVars);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo '<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;"><div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$fileName.'">'.$fileName.'</div>';
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo '<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$thisClass.'">'.$thisClass.'</div>';
            }
        }
        if(file_exists($fileName)) {
            include($fileName);
        }

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * Render block
     */
    public function renderView()
    {
        $this->setScriptPath(App_Main::getBaseDir('design'));
        //$params = array('_relative'=>true);
        $params = array();
        if ($area = $this->getArea()) {
            $params['_area'] = $area;
        }

        $templateName = App_Main::getDesign()->getTemplateFilename($this->getTemplate(), $params);

        $html = $this->fetchView($templateName);

        return $html;
    }

    /**
     * Render block HTML
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }

    /**
     * Get base url of the application
     */
    public function getBaseUrl()
    {
        if (!$this->_baseUrl) {
            $this->_baseUrl = App_Main::getBaseUrl();
        }
        return $this->_baseUrl;
    }

    /**
     * Get url of base javascript file
     * To get url of skin javascript file use getSkinUrl()
     */
    public function getJsUrl($fileName='')
    {
        if (!$this->_jsUrl) {
            $this->_jsUrl = App_Main::getBaseUrl('js');
        }
        return $this->_jsUrl.$fileName;
    }

}
