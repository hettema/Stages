<?php
/**
 * class Core_Model_Layout
 * Handles the front end layout and block views
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Layout extends Core_Model_Abstract
{    
    /**
     * Blocks registry
     */
    protected $_blocks = array();

    /**
     * Cache of block callbacks to output during rendering
     */
    protected $_output = array();

    /**
     * Layout area (f.e. admin, frontend)
     */
    protected $_area;

    /**
     * Helper blocks cache for this layout
     */
    protected $_helpers = array();

    /**
     * Output the blocks' directly to browser as oppose to return result
     */
    protected $_directOutput = false;

    protected $_layoutDir = '';
    protected $_layoutConfigFile = 'config/layout.xml';
    protected $_xml; //layout config

    public function __construct($data=array())
    {
       parent::__construct($data);
    }

    /**
     * Load the layout for the design area
     * The mehotd will load layout.xml the theme directory and blocks will be initialized
     * 
     * @return Core_Model_Layout 
     */
    public function loadLayoutConfig()
    {
        $this->_layoutConfigFile = App_Main::getDesign()->getLayoutFilename('layout.xml');
        if(!file_exists($this->_layoutConfigFile)) {
            App_Main::throwException('Layout config file not found. Cannot continue');
            exit ();
        }

        $this->_blocks = array();
        $this->_elementClass = App_Main::getModelClassName('core/layout_element');
        $this->setXml(simplexml_load_file($this->_layoutConfigFile, $this->_elementClass)->default->block);
        $this->generateBlocks();
        return $this;
    }

    /**
     * Set the layout config xml object
     * 
     * @param SimpleXMLElement $node
     * @return Core_Model_Layout 
     */
    public function setXml(SimpleXMLElement $node)
    {
        $this->_xml = $node;
        return $this;
    }
    
    /**
     * Get the xml node
     * 
     * @param string $path
     * @return type 
     */
    public function getNode($path=null)
    {
        if (!$this->_xml instanceof SimpleXMLElement) {
            return false;
        } elseif ($path === null) {
            return $this->_xml;
        }
    }
    
    /**
     * Generate block instances and stack them to the blocks registery
     * 
     * @param SimpleXMLElement $parent 
     */
    public function generateBlocks($parent=null)
    {
        if (empty($parent)) {
            $parent = $this->getNode();
        }
        foreach ($parent as $node) {
            $attributes = $node->attributes();
            if ((bool)$attributes->ignore) {
                continue;
            }
            switch ($node->getName()) {
                case 'block':
                    $this->_generateBlock($node, $parent);
                    $this->generateBlocks($node);
                    break;

                case 'reference':
                    $this->generateBlocks($node);
                    break;

                case 'action':
                    $this->_generateAction($node, $parent);
                    break;
            }
        }
    }

    /**
     * Gernerate block for the passed node
     * 
     * @param SimpleXMLElement $node
     * @param SimpleXMLElement $parent
     * @return Core_Model_Layout 
     */
    protected function _generateBlock($node, $parent)
    {
        if (!empty($node['class'])) {
            $className = (string)$node['class'];
        } else {
            $className = App_Main::getBlockClassName((string)$node['type']);
        }

        $blockName = (string)$node['name'];
        $block = $this->addBlock($className, $blockName);
        if (!$block) {
            return $this;
        }

        if (!empty($node['parent'])) {
            $parentBlock = $this->getBlock((string)$node['parent']);
        } else {
            $parentName = $parent->getBlockName();
            if (!empty($parentName)) {
                $parentBlock = $this->getBlock($parentName);
            }
        }
        if (!empty($parentBlock)) {
            $alias = isset($node['as']) ? (string)$node['as'] : '';
            if (isset($node['before'])) {
                $sibling = (string)$node['before'];
                if ('-'===$sibling) {
                    $sibling = '';
                }
                $parentBlock->insert($block, $sibling, false, $alias);
            } elseif (isset($node['after'])) {
                $sibling = (string)$node['after'];
                if ('-'===$sibling) {
                    $sibling = '';
                }
                $parentBlock->insert($block, $sibling, true, $alias);
            } else {
                $parentBlock->append($block, $alias);
            }
        }
        if (!empty($node['template'])) {
            $block->setTemplate((string)$node['template']);
        }

        if (!empty($node['output'])) {
            $method = (string)$node['output'];
            $this->addOutputBlock($blockName, $method);
        }

        return $this;
    }
    
    /**
     * Initialize the block instance and add it to the blocks registery
     * 
     * @param string $block
     * @param string $blockName
     * @return Core_Block_Abstract 
     */
    public function addBlock($block, $blockName)
    {
        try {
            $block = $this->_getBlockInstance($block);
        } catch (Exception $e) {
            return false;
        }

        $block->setNameInLayout($blockName);
        $block->setLayout($this);
        $this->_blocks[$blockName] = $block;

        return $block;
    }

    /**
     * Initialize the block instance and set the layout and other parameters
     * 
     * @param string $block
     * @param string $blockName
     * @return Core_Block_Abstract 
     */
    public function createBlock($type, $name='', array $attributes = array())
    {
        try {
            $block = $this->_getBlockInstance($type, $attributes);
        } catch (Exception $e) {
            App_Main::logException($e);
            return false;
        }
        $block->setType($type);
        $block->setNameInLayout($name);
        $block->addData($attributes);
        $block->setLayout($this);

        $this->_blocks[$name] = $block;

        return $this->_blocks[$name];
    }

    /**
     * Set the current area for the request frontend/backend
     * 
     * @param string $area
     * @return Core_Model_Layout 
     */
    public function setArea($area)
    {
        $this->_area = $area;
        return $this;
    }

    /**
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->_area;
    }

    /**
     * Set the direct output flag, enable/disable rendering using output buffer
     * 
     * @param bool $flag
     * @return Core_Model_Layout 
     */
    public function setDirectOutput($flag)
    {
        $this->_directOutput = $flag;
        return $this;
    }

    /**
     * 
     * @return bool 
     */
    public function getDirectOutput()
    {
        return $this->_directOutput;
    }

    /**
     * Add a new block instance to the stack
     * 
     * @param string $name
     * @param Core_Block_Abstract $block
     * @return Core_Model_Layout 
     */
    public function setBlock($name, $block)
    {
        $this->_blocks[$name] = $block;
        return $this;
    }

    /**
     * Remove a block form the layout by name
     * @param string $name
     * @return Core_Model_Layout 
     */
    public function unsetBlock($name)
    {
        $this->_blocks[$name] = null;
        unset($this->_blocks[$name]);
        return $this;
    }
    
    /**
     * Get an instance of the block from the block name
     * 
     * @param string $block
     * @param array $attributes
     * @return Core_Block_Abstract 
     */
    protected function _getBlockInstance($block, array $attributes=array())
    {
        $block = App_Main::getBlockInstance($block, $attributes);
        if(empty($block)) {
            App_Main::throwException('Invalid block type: %s', $block);
        }
        if (!$block instanceof Core_Block_Abstract) {
            App_Main::throwException('Invalid block type: %s', $block);
        }
        return $block;
    }

    /**
     *
     * @return array blocks 
     */
    public function getAllBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Get an initialized block from the blocks registery
     * 
     * @param string $name
     * @return Core_Block_Abstract 
     */
    public function getBlock($name)
    {
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    /**
     * Add an output block to outputblock stack for the current loaded layout
     * By default and normally there will be only one output block, the 'root' block 
     * 
     * @param string $blockName
     * @param string $method
     * @return Core_Model_Layout 
     */
    public function addOutputBlock($blockName, $method='toHtml')
    {
        $this->_output[$blockName] = array($blockName, $method);
        return $this;
    }

    /**
     * Remove an outputblock from the output block stack
     * 
     * @param string $blockName
     * @return Core_Model_Layout 
     */
    public function removeOutputBlock($blockName)
    {
        unset($this->_output[$blockName]);
        return $this;
    }

    /**
     * Get the rendered output for the current loaded layout
     * @return string  
     */
    public function getOutput()
    {
        $out = '';
        if (!empty($this->_output)) {
            foreach ($this->_output as $callback) {
                $out .= $this->getBlock($callback[0])->$callback[1]();
            }
        }
        return $out;
    }

    /**
     * Get messages block
     *
     * @return Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        if ($block = $this->getBlock('messages')) {
            return $block;
        }
        return $this->createBlock('core/messages', 'messages');
    }
   
    /**
     * get the helper object
     *
     * @param   string $name
     * @return  Core_Helper_Abstract
     */
    public function helper($name)
    {
        $helper = App_Main::getHelper($name);
        if (!$helper) { return false; }

        return $helper->setLayout($this);
    }
}
?>
