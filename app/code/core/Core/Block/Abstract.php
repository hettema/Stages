<?php
/**
 * class Core_Block_Abstract
 *
 * To generate a block define, data source class, data source class method, parameters array and block template
 * 
 * @package Core
 * @category Block
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
abstract class Core_Block_Abstract extends Core_Model_Object
{

    /**
     * Name of the block in layout
     */
    protected $_name;

    /**
     * Layout of the block
     * @var Core_Model_Layout
     */
    protected $_layout;

    /**
     * Parent block
     * @var Core_Block_Abstract
     */
    protected $_parentBlock;

    /**
     * Stirng alias of this block to refer from parent
     * @var string
     */
    protected $_alias;

    /**
     * Hold the references to child block objects
     */
    protected $_children = array();

    /**
     * Sorted child block objects
     */
    protected $_sortedChildren = array();

    /**
     * Children blocks HTML cache array
     */
    protected $_childrenHtmlCache = array();

    /**
     * Global request object
     *
     * @var Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * Global session object
     *
     * @var Core_Model_Session
     */
    protected $_session;

    /**
     * Messages block object instance
     */
    protected $_messagesBlock = null;

    /**
     * Whether this block was not named explicitly
     */
    protected $_isAnonymous = false;


    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     */
    protected function _construct()
    {
        /**
         * Please override this one instead of overriding real __construct constructor
         */
    }

    /**
     * Get the global request object
     *
     * @return Core_Controller_Request_Http
     */
    public function getRequest()
    {
        if($request = App_Main::getRequest()) {
            $this->_request = $request;
        } else {
            throw new Exception("Can't retrieve request object");
        }
        return $this->_request;
    }

    /**
     * Retrieve session object
     *
     * @return Core_Model_Session
     */
    public function getSession()
    {
        if(!$this->_session) {
            $this->_session = App_Main::getSession();
        }
        return $this->_session;
    }

    /**
     * Get the parent block
     */
    public function getParentBlock()
    {
        return $this->_parentBlock;
    }

    /**
     * Set the parent block
     * @param Core_Block_Abstract
     * @return Core_Block_Abstract
     */
    public function setParentBlock(Core_Block_Abstract $block)
    {
        $this->_parentBlock = $block;
        return $this;
    }

    /**
     * Get the action object for the served request
     * @return Core_Controller_Action
     */
    public function getAction()
    {
        return App_Main::getRequest()->getAction();
    }

    /**
     * Set the layout object
     * 
     * @param Core_Model_Layout
     * @return Core_Block_Abstract
     */
    public function setLayout(Core_Model_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Get the blocks layout object instance
     * 
     * @return Core_Model_Layout 
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get the block's name 
     * 
     * @return string block name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the block's name in the layout
     * 
     * @param string $name
     * @return Core_Block_Abstract 
     */
    public function setName($name)
    {
        if (!empty($this->_name) && $this->getLayout()) {
            $this->getLayout()
            ->unsetBlock($this->_name)
            ->setBlock($name, $this);
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Get the sorted child blocks
     * 
     * @return array child block instances
     */
    public function getSortedChildren()
    {
        return $this->_sortedChildren;
    }

    /**
     * Set an attribute=>value for the block instance
     * 
     * @param string attribute name
     * @param mixed value
     * @return Core_Block_Abstract
     */
    public function setAttribute($name, $value=null)
    {
        return $this->setData($name, $value);
    }

    /**
     * Set the child block under the block instance
     * 
     * @param string child block alias
     * @param Core_Block_Abstract|string child block
     * @return Core_Block_Abstract 
     */
    public function setChild($alias, $block)
    {
        if (is_string($block)) {
            $block = $this->getLayout()->getBlock($block);
        }
        if (!$block) { return $this; }
        
        if ($block->getIsAnonymous()) {

            $suffix = $block->getAnonSuffix();
            if (empty($suffix)) {
                $suffix = 'child'.sizeof($this->_children);
            }
            $blockName = $this->getNameInLayout().'.'.$suffix;

            if ($this->getLayout()) {
                $this->getLayout()
                ->unsetBlock($block->getNameInLayout())
                ->setBlock($blockName, $block);
            }

            $block->setNameInLayout($blockName);
            $block->setIsAnonymous(false);

            if (empty($alias)) {
                $alias = $blockName;
            }
        }

        $block->setParentBlock($this);
        $block->setBlockAlias($alias);

        $this->_children[$alias] = $block;

        return $this;
    }

    /**
     * Get the child block instance by name
     * 
     * @param string block name
     * @return Core_Block_Abstract
     */
    public function getChild($name='')
    {
        if (''===$name) {
            return $this->_children;
        } elseif (isset($this->_children[$name])) {
            return $this->_children[$name];
        }
        return false;
    }

    /**
     * Get HTML view for a child block
     * If name is not specified, this will return the HTML of all the child blocks
     * 
     * @param string $name
     * @param bool $useCache
     * @param bool $sorted
     * @return type 
     */
    public function getChildHtml($name='', $useCache=true, $sorted=false)
    {
        if ('' === $name) {
            if ($sorted) {
                $children = array();
                foreach ($this->getSortedChildren() as $childName) {
                    $children[$childName] = $this->getLayout()->getBlock($childName);
                }
            } else {
                $children = $this->getChild();
            }
            $out = '';
            foreach ($children as $child) {
                $out .= $this->_getChildHtml($child->getBlockAlias(), $useCache);
            }
            return $out;
        } else {
            return $this->_getChildHtml($name, $useCache);
        }
    }

    /**
     * Get the HTML view for a child block's child block
     * 
     * @param string $name
     * @param string $childName
     * @param bool $useCache
     * @param bool $sorted
     * @return string HTML 
     */
    public function getChildChildHtml($name, $childName = '', $useCache = true, $sorted = false)
    {
        if (empty($name)) {
            return '';
        }
        $child = $this->getChild($name);
        if (!$child) {
            return '';
        }
        return $child->getChildHtml($childName, $useCache, $sorted);
    }

    /**
     * Get sorted child blocks
     * @return array
     */
    public function getSortedChildBlocks()
    {
        $children = array();
        foreach ($this->getSortedChildren() as $childName) {
            $children[$childName] = $this->getLayout()->getBlock($childName);
        }
        return $children;
    }

    /**
     * Get the child block's HTML view
     * 
     * @param string
     * @param bool
     * @return string
     */
    protected function _getChildHtml($name, $useCache=true)
    {
        if ($useCache && isset($this->_childrenHtmlCache[$name])) {
            return $this->_childrenHtmlCache[$name];
        }

        $child = $this->getChild($name);

        if (!$child) {
            $html = '';
        } else {
            $this->_beforeChildToHtml($name, $child);
            $html = $child->toHtml();
        }

        $this->_childrenHtmlCache[$name] = $html;
        return $html;
    }

    /**
     * Prepare block before generating the HTML view
     * 
     * @param string $name
     * @param Core_Block_Abstract $child 
     */
    protected function _beforeChildToHtml($name, $child)
    {
    }

    /**
     * Get the block HTML view
     * 
     * @param string block name
     * @return string block HTML
     */
    public function getBlockHtml($name)
    {
        if (!($layout = $this->getLayout()) && !($layout = App_Main::getControllerFrontend()->getLayout())) {
            return '';
        }
        if (!($block = $layout->getBlock($name))) {
            return '';
        }
        return $block->toHtml();
    }

    /**
     * Insert child block
     *
     * @param   Core_Block_Abstract|string $block
     * @param   string $siblingName
     * @param   boolean $after
     * @param   string $alias
     * @return  Core_Block_Abstract
     */
    public function insert($block, $siblingName='', $after=false, $alias='')
    {
        if (is_string($block)) {
            $block = $this->getLayout()->getBlock($block);
        }
        if (!$block) {
            return $this;
        }
        if ($block->getIsAnonymous()) {
            $this->setChild('', $block);
            $name = $block->getNameInLayout();
        } elseif ('' != $alias) {
            $this->setChild($alias, $block);
            $name = $block->getNameInLayout();
        } else {
            $name = $block->getNameInLayout();
            $this->setChild($name, $block);
        }

        if (''===$siblingName) {
            if ($after) {
                array_push($this->_sortedChildren, $name);
            }
            else {
                array_unshift($this->_sortedChildren, $name);
            }
        } else {
            $key = array_search($siblingName, $this->_sortedChildren);
            if (false!==$key) {
                if ($after) {
                    $key++;
                }
                array_splice($this->_sortedChildren, $key, 0, $name);
            } else {
                if ($after) {
                    array_push($this->_sortedChildren, $name);
                }
                else {
                    array_unshift($this->_sortedChildren, $name);
                }
            }
        }

        return $this;
    }

    /**
     * Append a child block
     * 
     * @param Core_Block_Abstract block
     * @param string block alias
     * @return Core_Block_Abstract
     */
    public function append($block, $alias='')
    {
        $this->insert($block, '', true, $alias);
        return $this;
    }

    /**
     * Called Before rendering html
     * 
     * @return Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * It is a final method, but can be overriden in descendants
     *
     * @return string
     */
    final public function toHtml()
    {
        if (!($html = $this->_loadCache())) {

            $this->_beforeToHtml();
            $html = $this->_toHtml();
            $this->_saveCache($html);
        }

        $html = $this->_afterToHtml($html);

        return $html;
    }

    /**
     * Processing block HTML after rendering
     *
     * @param   string html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        return $html;
    }

    /**
     * Can be overriden in descendants
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }

    /**
     * Get the url model class, this differentiates the front end and backend/admin url models
     *
     * @return string
     */
    protected function _getUrlModelClass()
    {
        return 'core/url';
    }

    /**
     *
     * @return Core_Model_Url 
     */
    protected function _getUrlModel()
    {
        return App_Main::getModel($this->_getUrlModelClass());;
    }

    /**
     * Get url by route and parameters
     * 
     * @param string route
     * @param array url parameters
     * @return string url
     */
    public function getUrl($route='', $params=array())
    {
        return $this->_getUrlModel()->getUrl($route, $params);
    }

    /**
     * Get base64-encoded url by route and parameters     
     *  
     * @param string route
     * @param array url parameters
     * @return string base64 encoded url
     */
    public function getUrlBase64($route='', $params=array())
    {
        return App_Main::helper('core')->urlEncode($this->getUrl($route, $params));
    }

    /**
     * Get the encoded url      
     *  
     * @param string route
     * @param array url parameters
     * @return string base64 encoded url
     */
    public function getUrlEncoded($route = '', $params = array())
    {
        return App_Main::helper('core')->urlEncode($this->getUrl($route, $params));
    }

    /**
     * Get url of skins files, Url is generated based on the current theme and area
     * 
     * @param string file
     * @param array parameters
     * @return string
     */
    public function getSkinUrl($file=null, array $params=array())
    {
        return  App_Main::getDesign()->getSkinUrl($file, $params);
    }

    /**
     * Get messages block
     * 
     * @return Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        if (is_null($this->_messagesBlock)) {
            return $this->getLayout()->getMessagesBlock();
        }
        return $this->_messagesBlock;
    }

    /**
     * Set the messages block
     * 
     * @return Core_Block_Abstract
     */
    public function setMessagesBlock(Core_Block_Messages $block)
    {
        $this->_messagesBlock = $block;
        return $this;
    }

    /**
     * Get the helper class for the module tyepe
     * 
     * @param string module
     * @return Core_Helper_Abstract
     */
    public function helper($name)
    {
        if ($this->getLayout()) {
            return $this->getLayout()->helper($name);
        }
        return App_Main::getHelper($name);
    }

    /**
     * Get module name of block
     * 
     * @return string module name
     */
    public function getModuleName()
    {
        $module = $this->getData('module_name');
        if (is_null($module)) {
            $class = get_class($this);
            $module = substr($class, 0, strpos($class, '_Block'));
            $this->setData('module_name', $module);
        }
        return $module;
    }

    /**
     * Translate wrapper method to translate block sentence
     */
    public function __()
    {
        $args = func_get_args();
        return App_Main::getTranslator()->translate($args);
    }

    /**
     * Get Key for caching block content
     */
    public function getCacheKey()
    {
        if (!$this->hasData('cache_key')) {
            $this->setCacheKey($this->getNameInLayout());
        }
        return $this->getData('cache_key');
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
    public function getCacheTags()
    {
        if (!$this->hasData('cache_tags')) {
            $tags = array();
        } else {
            $tags = $this->getData('cache_tags');
        }
        $tags[] = 'block_html';
        return $tags;
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }
        return $this->getData('cache_lifetime');
    }

    /**
     * Load content cache
     */
    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime()) || !App_Main::useCache('block_html')) {
            return false;
        }
        return App_Main::getCacheFactory()->loadCache($this->getCacheKey());
    }

    /**
     * Enter description here...
     */
    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime()) || !App_Main::useCache('block_html')) {
            return false;
        }
        App_Main::getCacheFactory()->saveCache($data, $this->getCacheKey(), $this->getCacheTags(), $this->getCacheLifetime());
        return $this;
    }

    /**
     * Escape html entities
     * 
     * @return string
     */
    public function htmlEscape($data, $allowedTags = null)
    {
        return $this->helper('core')->htmlEscape($data, $allowedTags);
    }

    /**
     * Escape html entities in url
     * 
     * @return string
     */
    public function urlEscape($data)
    {
        return $this->helper('core')->urlEscape($data);
    }

    /**
     * Escape quotes in java scripts
     */
    public function jsQuoteEscape($data, $quote = '\'')
    {
        return $this->helper('core')->jsQuoteEscape($data, $quote);
    }

    /**
     * Get the block name used in the layout\
     * @return stirng 
     */
    public function getNameInLayout()
    {
        return $this->_getData('name_in_layout');
    }

    /**
     * Get the child block's total count
     * 
     * @return int count 
     */
    public function countChildren()
    {
        return count($this->_children);
    }

    /**
     * Prepare url for save to cache
     * 
     * @return Core_Block_Abstract
     */
    protected function _beforeCacheUrl()
    {
        if (App_Main::useCache('block_html')) {
            App_Main::getCacheFactory()->setUseSessionVar(true);
        }
        return $this;
    }

    /**
     * Replace URLs from cache
     * 
     * @return string HTML
     */
    protected function _afterCacheUrl($html)
    {
        if (App_Main::useCache('block_html')) {
            App_Main::getCacheFactory()->setUseSessionVar(false);
        }
        return $html;
    }
}
