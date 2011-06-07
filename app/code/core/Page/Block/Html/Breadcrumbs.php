<?php
/**
 * class Page_Block_Html_Breadcrumbs
 * HTML breadcrumbs block:- controls the breadcrub view for the page
 * 
 * @package Page
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Page_Block_Html_Breadcrumbs extends Core_Block_Template
{
       /**
     * Array of breadcrumbs
     *
     * array(
     *  [$index] => array(
     *                  ['label']
     *                  ['title']
     *                  ['link']
     *                  ['first']
     *                  ['last']
     *              )
     * )
     *
     * @var array
     */
    protected $_crumbs = null;

    /**
     * Constructor class
     * Set the brudcrumb html template file on initialization
     * 
     */
    function __construct()
    {
    	parent::__construct();
    	$this->setTemplate('page/html/breadcrumbs.phtml');
    }

    /**
     * Add a new crumb
     * 
     * @param string $crumbName
     * @param string $crumbInfo
     * @param bool $after
     * @return Page_Block_Html_Breadcrumbs 
     */
    function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        $this->_prepareArray($crumbInfo, array('label', 'title', 'link', 'first', 'last', 'readonly'));
        if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
    	   $this->_crumbs[$crumbName] = $crumbInfo;
        }
    	return $this;
    }

    /**
     * Get the breadcrumb HTML view
     * 
     * @return string HTML 
     */
    protected function _toHtml()
    {
        if (is_array($this->_crumbs)) {
            reset($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['first'] = true;
            end($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['last'] = true;
        }
    	$this->assign('crumbs', $this->_crumbs);
    	return parent::_toHtml();
    }
    
}