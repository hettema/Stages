<?php
/**
 * class Core_Helper_Abstract
 * Abstact class for helper objects
 * 
 * @package Core
 * @category Helper
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
abstract class Core_Helper_Abstract
{
    /**
     * Helper module name
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Layout model object
     *
     * @var Core_Model_Layout
     */
    protected $_layout;

    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Http
     */
    protected function _getRequest()
    {
        if (!$this->_request) {
            $this->_request = App_Main::getRequest();
        }
        return $this->_request;
    }

    /**
     * Load cache data by cache id
     *
     * @param   string $id
     * @return  mixed
     */
    protected function _loadCache($id)
    {
        return App_Main::getCacheFactory()->loadCache($id);
    }

    /**
     * Save cache
     *
     * @param   mixed $data
     * @param   string $id
     * @param   array $tags
     * @return  Core_Helper_Abstract
     */
    protected function _saveCache($data, $id, $tags=array(), $lifeTime=false)
    {
        App_Main::getCacheFactory()->saveCache($data, $id, $tags, $lifeTime);
        return $this;
    }

    /**
     * Remove cache by cache id
     *
     * @param   string $id
     * @return  Core_Helper_Abstract
     */
    protected function _removeCache($id)
    {
        App_Main::getCacheFactory()->removeCache($id);
        return $this;
    }

    /**
     * Clean cache
     *
     * @param   array $tags
     * @return  Core_Helper_Abstract
     */
    protected function _cleanCache($tags=array())
    {
        App_Main::getCacheFactory()->cleanCache($tags);
        return $this;
    }

    /**
     * Get helper module name from the helper class
     *
     * @return string
     */
    protected function _getModuleName()
    {
        if (!$this->_moduleName) {
            $class = get_class($this);
            $this->_moduleName = substr($class, 0, strpos($class, '_Helper'));
        }
        return $this->_moduleName;
    }

    /**
     * Translate string
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        return App_Main::getTranslator()->translate($args);
    }

    /**
     * Escape html entities
     *
     * @param   mixed $data
     * @param   array $allowedTags
     * @return  mixed
     */
    public function htmlEscape($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
            	$result[] = $this->htmlEscape($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    /**
     * Escape html entities in url
     *
     * @param string $data
     * @return string
     */
    public function urlEscape($data)
    {
        return htmlspecialchars($data);
    }

    /**
     * Escape quotes in java script
     *
     * @param moxed $data
     * @param string $quote
     * @return mixed
     */
    public function jsQuoteEscape($data, $quote='\'')
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = str_replace($quote, '\\'.$quote, $item);
            }
            return $result;
        }
        return str_replace($quote, '\\'.$quote, $data);
    }

    /**
     * Get url
     * Wraper function for Core_Model_Url::getUrl
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        return App_Main::getUrl($route, $params);
    }

    /**
     * Set layout instance
     *
     * @param   Core_Model_Layout $layout
     * @return  Core_Helper_Abstract
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Get layout model object
     *
     * @return Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     *  base64_encode url
     * 
     *  @param    string $url
     *  @return	  string
     */
    public function urlEncode($url)
    {
        return strtr(base64_encode($url), '+/=', '-_,');
    }

    /**
     *  base64_decode url
     *
     *  @param    string $url
     *  @return	  string
     */
    public function urlDecode($url)
    {
        return base64_decode(strtr($url, '-_,', '+/='));
    }

    /**
     *   Translate array entries
     *
     *  @param    array $arr
     *  @return	  array
     */
    public function translateArray($arr = array())
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = self::translateArray($v);
            } elseif ($k === 'label') {
                $v = self::__($v);
            }
            $arr[$k] = $v;
        }
        return $arr;
    }
}