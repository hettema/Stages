<?php
/**
 * class Core_Controller_Frontend
 * 
 * @package Core
 * @category Controller-Frontend
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Frontend extends Core_Model_Object
{
    protected $_defaults = array();

    /**
     * Available routers array
     */
    protected $_routers = array();

    protected $_urlCache = array();

    function __construct()
    {
        $this->init();
    }
    
    public function getRequest()
    {
        return App_Main::getRequest();
    }
    
    public function getResponse()
    {
        return App_Main::getResponse();
    }
    
    public function addRouter($name, Core_Controller_Router_Abstract $router)
    {
        $router->setFront($this);
        $this->_routers[$name] = $router;
        return $this;
    }
    
    public function getRouter($name)
    {
        if (isset($this->_routers[$name])) {
            return $this->_routers[$name];
        }
        return false;
    }
    
    public function setDefault($key, $value=null)
    {
        if (is_array($key)) {
            $this->_defaults = $key;
        } else {
            $this->_defaults[$key] = $value;
        }
        return $this;
    }

    public function getDefault($key=null)
    {
        if (is_null($key)) {
            return $this->_defaults;
        } elseif (isset($this->_defaults[$key])) {
            return $this->_defaults[$key];
        }
        return false;
    }

    public function init()
    {        
        $routersInfo = array('standard'=>array('class'=>'Core_Controller_Router_Standard', 'area'=>'frontend'),
                             'admin'=>array('class'=>'Core_Controller_Router_Admin', 'area'=>'admin')
                            );

        foreach ($routersInfo as $routerCode => $routerInfo) {
            if (isset($routerInfo['disabled']) && $routerInfo['disabled']) {
            	continue;
            }
            if (isset($routerInfo['class'])) {
            	$router = new $routerInfo['class'];
            	if (isset($routerInfo['area'])) {
            		$router->collectRoutes($routerInfo['area'], $routerCode);
            	}
            	$this->addRouter($routerCode, $router);
            }
        }
       
        // Add default router at the last
        $default = new Core_Controller_Router_Default();
        $this->addRouter('default', $default);

        return $this;
    }

    public function preDispatch()
    {
        App_Main::getModel('core/url_rewrite')->rewrite();
        
        //Initialize the display singleton prior to loading layout and initializing controllers
        App_Main::getDesign();
        
        return $this;
    }

    public function dispatch()
    {
        $this->preDispatch();

        $request = $this->getRequest();
        $request->setPathInfo()->setDispatched(false);

        $i = 0;
        while (!$request->isDispatched() && $i++<100) {
            foreach ($this->_routers as $router) {
                if ($router->match($this->getRequest())) {
                    break;
                }
            }
        }
        if ($i>100) {
            App_Main::throwException('Front controller reached 100 router match iterations');
        }

        $this->getResponse()->sendResponse();

        $this->postDispatch();
        return $this;
    }
    
    public function postDispatch()
    {
        
    }

    public function getRouterByRoute($routeName)
    {
        // empty route supplied - return base url
        if (empty($routeName)) {
            $router = $this->getRouter('admin');
        } elseif ($this->getRouter('admin')->getFrontNameByRoute($routeName)) {
            // try standard router url assembly
            $router = $this->getRouter('admin');
        } elseif ($this->getRouter('standard')->getFrontNameByRoute($routeName)) {
            // try standard router url assembly
            $router = $this->getRouter('standard');
        } elseif ($router = $this->getRouter($routeName)) {
            // try custom router url assembly
        } else {
            // get default router url
            $router = $this->getRouter('default');
        }

        return $router;
    }

    public function getRouterByFrontName($frontName)
    {
        // empty route supplied - return base url
        if (empty($frontName)) {
            $router = $this->getRouter('admin');
        } elseif ($this->getRouter('admin')->getRouteByFrontName($frontName)) {
            // try standard router url assembly
            $router = $this->getRouter('admin');
        } elseif ($this->getRouter('standard')->getRouteByFrontName($frontName)) {
            // try standard router url assembly
            $router = $this->getRouter('standard');
        } elseif ($router = $this->getRouter($frontName)) {
            // try custom router url assembly
        } else {
            // get default router url
            $router = $this->getRouter('default');
        }

        return $router;
    }
}
?>