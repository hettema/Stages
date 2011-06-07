<?php
/** 
 * class Core_Model_Design
 * Design model to control the front view design
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Design extends Core_Model_Abstract
{
    const DEFAULT_AREA = 'frontend';
    const DEFAULT_THEME = 'default';
    const FALLBACK_THEME = 'default';
    
    protected $_area;

    protected $_skinBaseUrl;
    
	protected function _construct()
	{
		//$this->_init('core/design');
        $this->setTheme(self::DEFAULT_THEME);
	}
    
    /**
	 * Set the front area
     * @param string area frontend, backend
	 */
	public function setArea($area)
	{
		$this->_area = $area;
		return $this;
	}

	/**
	 * Get the front render area
	 */
	public function getArea()
	{
		if (is_null($this->_area)) {
			$this->_area = self::DEFAULT_AREA;
		}
		return $this->_area;
	}

    /**
     *
     * @return bool 
     */
    public function isThemeLocked()
    {
        return $this->getLockTheme();
    }

    /**
     * Set the currnet theme locked so it wont be changed at a later point
     */
    public function lockTheme()
    {
        $this->setLockTheme(true);
    }

    /**
     *
     * @return Core_Model_Design 
     */
    public function validate()
    {
        $this->getResource()->validate($this);
        return $this;
    }

    /**
     * Update the paramer values with design specific data
     * 
     * @param array $params
     * @return Core_Model_Design 
     */
    public function updateParamDefaults(array &$params)
	{
		if (empty($params['_area'])) {
			$params['_area'] = $this->getArea();
		}
		if (empty($params['_theme'])) {
			$params['_theme'] = $this->getTheme();
		}
    	if (empty($params['_default'])) {
    		$params['_default'] = false;
    	}
		return $this;
	}

    /**
     * Get existing file name with fallback to default theme
     *
     * $params['_type'] is required
     * @param string file
     * @param array params
     * @return string file name
     */
    public function getFilename($file, array $params)
    {
    	$this->updateParamDefaults($params);
		$filename = $this->validateFile($file, $params);
        //fallback to default theme file
        if (false===$filename) {
			$params['_theme'] = self::FALLBACK_THEME;
			$filename = $this->validateFile($file, $params);
			if (false===$filename) {
        		if (self::DEFAULT_THEME === $params['_theme']) {
        			return $params['_default'];
        		}
    			$params['_theme'] = self::DEFAULT_THEME;
    			$filename = $this->validateFile($file, $params);
    			if (false===$filename) {
    				return $params['_default'];
    			}
			}
		}
		return $filename;
    }
    
	/**
     * Get absolute file path for requested file or false if doesn't exist
     *
     * Possible params:
     * - _type:
     * 	 - layout
     *   - template
     *   - skin
     *   - translate
     * - _package: design package, if not set = default
     * - _theme: if not set = default
     * - _file: path relative to theme root
     *
     * @param string $file
     * @param array $params
     * @return string|boolean
     *
     */
    public function validateFile($file, array $params)
    {
    	switch ($params['_type']) {
    		case 'skin':
    			$dirName = $this->getSkinBaseDir($params);
    		break;

    		case 'locale':
    			$dirName = $this->getLocaleBasedir($params);
    		break;
    		case 'layout':
    			$dirName = $this->getLayoutBaseDir($params);
    		break;

    		default:
    			$dirName = $this->getBaseDir($params);
    		break;
    	}
        
        if(!file_exists($dirName . DS . $file)) return false;
        return $dirName . DS . $file;
    }

    /**
     * Get the base theme design directory
     * 
     * @param array $params
     * @return string base dir
     */
	public function getBaseDir(array $params)
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? App_Main::getBaseDir('design').DS : ''). $params['_area'].DS.$params['_theme'].DS.$params['_type'];
		return $baseDir;
	}
    
    /**
     * Get skin directory under the loaded theme
     * 
     * @param array $params
     * @return string skin directory
     */
    public function getSkinBaseDir(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? App_Main::getBaseDir('design').DS : ''). $params['_area'].DS.$params['_theme'].DS.'skin';
		return $baseDir;
	}
    
    /**
     * Get the locale directory for the loaded theme
     *
     * @param array $params
     * @return string locale directory
     */
    public function getLocaleBaseDir(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? App_Main::getBaseDir('design').DS : ''). $params['_area'].DS.$params['_theme'].DS.'locale';
		return $baseDir;
	}
    
    /**
     * Get the loaded themes layout directory
     *
     * @param array $params
     * @return string theme layout directory
     */
    public function getLayoutBaseDir(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? App_Main::getBaseDir('design').DS : ''). $params['_area'].DS.$params['_theme'];
		return $baseDir;
	}
    
    /**
     * Get the layout file name under the loaded theme
     * Used to load the layout.xml froom Core_Model_Layout
     * 
     * @param type $file
     * @param array $params
     * @return type 
     */
    public function getLayoutFilename($file, array $params=array())
    {
    	$params['_type'] = 'layout';
    	return $this->getFilename($file, $params);
    }
    
    /**
     * Get the template file under the themes template directory
     * 
     * @param string $file
     * @param array $params
     * @return string filename 
     */
    public function getTemplateFilename($file, array $params=array())
    {
    	$params['_type'] = 'template';
    	return $this->getFilename($file, $params);
    }

    /**
     *
     * @param string $file
     * @param array $params
     * @return type locale file
     */
    public function getLocaleFileName($file, array $params=array())
    {
        $params['_type'] = 'locale';
    	return $this->getFilename($file, $params);
    }

    /**
     * Get the skin base url for the loaded theme
     * 
     * @param array $params
     * @return string skin url 
     */
	public function getSkinBaseUrl(array $params=array())
	{
		$this->updateParamDefaults($params);
        if(!$this->_skinBaseUrl) {
            $this->_skinBaseUrl = App_main::getModel('core/url')->getDirectUrl('design', array('_nosid'=>true));
        }
		return $this->_skinBaseUrl .'/'. $params['_area'].'/'.$params['_theme'].'/'.'skin/';;
	}
    
    /**
     * Get skin file url
     * 
     * @param string file
     * @param array params
     * @return skin file url
     */
    public function getSkinUrl($file=null, array $params=array())
    {
    	if (empty($params['_type'])) {
    		$params['_type'] = 'skin';
    	}
    	if (empty($params['_default'])) {
    		$params['_default'] = false;
    	}
    	$this->updateParamDefaults($params);
    	if (!empty($file)) {
			$filename = $this->validateFile($file, $params);			
            if (false===$filename) {
                $params['_theme'] = self::FALLBACK_THEME;
                $filename = $this->validateFile($file, $params);
                if (false===$filename) {
                    if (self::DEFAULT_THEME === $params['_theme']) {
                        return $params['_default'];
                    }
                    $params['_theme'] = self::DEFAULT_THEME;
                    $filename = $this->validateFile($file, $params);
                    if (false===$filename) {
                        return $params['_default'];
                    }
                }
            }
    	}
        if(empty($filename)) return $params['_default'];
        $version = defined('APP_VERSION') ? "?".constant('APP_VERSION') : '?1.0';
        
    	return $this->getSkinBaseUrl($params).(!empty($file) ? $file : ''). $version;
    }
}
?>
