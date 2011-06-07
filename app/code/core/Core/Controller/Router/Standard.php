<?php
/**
 * class Core_Controller_Router_Standard
 * 
 * @package Core
 * @category Controller-Router
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Router_Standard extends Core_Controller_Router_Abstract
{
    protected $_modules = array();
    protected $_routes = array();
    protected $_dispatchData = array();

    public function collectRoutes($configArea, $useRouterName)
    {
        $routersConfigModules = array(
                                      array('frontName'=>'cms', 'routeName'=>'cms','module'=>'Cms', 'codepool'=>'core'),
                                      array('frontName'=>'core', 'routeName'=>'core','module'=>'Core', 'codepool'=>'core'),
                                      array('frontName'=>'stages', 'routeName'=>'stages', 'module'=>'Stages', 'codepool'=>'local'),
                                      array('frontName'=>'project', 'routeName'=>'project', 'module'=>'Project', 'codepool'=>'local')
                                     );
        if(empty($routersConfigModules)) { return $this; }
        $routersModules = $routersConfigModules;

        foreach($routersModules as $module) {
            $this->addModule($module['frontName'], array($module['module']), $module['routeName'], $module['codepool']);
        }

        return $this;
    }

    public function fetchDefault()
    {
        $this->getFront()->setDefault(array(
            'module'     => 'core',
            'controller' => 'index',
            'action'     => 'index'
        ));
    }

    /**
     * checking if this admin if yes then we don't use this router
     *
     * @return bool
     */
    protected function _beforeModuleMatch()
    {
        /*if (App_Main::isAdmin()) {
            return false;
        }*/
        return true;
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        //checkings before even try to findout that current module should use this router
        if (!$this->_beforeModuleMatch()) {
            return false;
        }

        $this->fetchDefault();
        $front = $this->getFront();

        $p = explode('/', trim($request->getPathInfo(), '/'));

        // get module name
        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        } else {
            if(!empty($p[0])) {
                $module = $p[0];
            } else {
                $module = $this->getFront()->getDefault('module');
                $request->setAlias('rewrite_request_path', '');
            }
        }
        if (!$module) {
            if (App_Main::isAdmin()) {
                $module = 'admin';
            }
        }
        if(empty($module)) return false;


        // Searching router args by module name from route using it as key
        $modules = $this->getModuleByFrontName($module);
        
        // If we did not found anything  we searching exact this module name in array values
        if ($modules === false) {
            if ($moduleFrontName = $this->getModuleByName($module, $this->_modules)) {
                $modules = array($module);
                $module = $moduleFrontName;
            }            
        }
        if(empty($modules)) { return false; }
        
        /**
         * Going through modules to find appropriate controller
         */
        $found = false;
        foreach ($modules as $realModule) {
            $request->setRouteName($this->getRouteByFrontName($module));

            // get controller name
            if ($request->getControllerName()) {
                $controller = $request->getControllerName();
            } else {
                if (!empty($p[1])) {
                    $controller = $p[1];
                } else {
                    $controller = $front->getDefault('controller');
                    $request->setAlias(
                        'rewrite_request_path',
                        ltrim($request->getOriginalPathInfo(), '/')
                    );
                }
            }

            // get action name
            if (empty($action)) {
                if ($request->getActionName()) {
                    $action = $request->getActionName();
                } else {
                    $action = !empty($p[2]) ? $p[2] : $front->getDefault('action');
                }
            }

            //checking if this place should be secure
            $this->_checkShouldBeSecure($request, '/'.$module.'/'.$controller.'/'.$action);

            $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
            if (!$controllerClassName) {
                continue;
            }

            // instantiate controller class
            $controllerInstance = new $controllerClassName($request, $front->getResponse());

            if (!$controllerInstance->hasAction($action)) {
                continue;
            }

            $found = true;
            break;
        }

        /**
         * if we did not found any siutibul
         */
        if (!$found) {
            if ($this->_noRouteShouldBeApplied()) {
                $controller = 'index';
                $action = 'noroute';

                $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
                if (!$controllerClassName) {
                    return false;
                }

                // instantiate controller class
                $controllerInstance = new $controllerClassName($request, $front->getResponse());

                if (!$controllerInstance->hasAction($action)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // set values only after all the checks are done
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($realModule);

        // set parameters from pathinfo
        for ($i=3, $l=sizeof($p); $i<$l; $i+=2) {
            $request->setParam($p[$i], isset($p[$i+1]) ? $p[$i+1] : '');
        }

        // dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);

        return true;
    }

    /**
     * Allow to control if we need to enable norout functionality in current router
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Generating and validate the controller class file,
     * if the file exists include (if needed) it and return class name
     *
     * @return mixed
     */
    protected function _validateControllerClassName($realModule, $controller)
    {
        $controllerFileName = $this->getControllerFileName($realModule, $controller);
        if (!$this->validateControllerFileName($controllerFileName)) {
            return false;
        }

        $controllerClassName = $this->getControllerClassName($realModule, $controller);
        if (!$controllerClassName) {
            return false;
        }

        // include controller file if needed
        if (!$this->_inludeControllerClass($controllerFileName, $controllerClassName)) {
            return false;
        }

        return $controllerClassName;
    }

    /**
     * Get the controller filename
     * 
     * @param string $realModule
     * @param string $controller
     * @return string 
     */
    public function getControllerFileName($realModule, $controller)
    {
        $parts = explode('_', $realModule);
        $realModule = implode('_', array_splice($parts, 0, 2));
        $file = App_Main::getModuleDir('controller', $realModule);
        if (count($parts)) {
            $file .= DS . implode(DS, $parts);
        }
        $file .= DS.uc_words($controller, DS). 'Controller.php';
        return $file;
    }


    /**
     * Including controller class
     * checks for existense of $controllerFileName
     *
     * @param string $controllerFileName
     * @param string $controllerClassName
     * @return bool
     */
    protected function _inludeControllerClass($controllerFileName, $controllerClassName)
    {
        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return false;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                throw App_Main::exception('Core', 'Controller file was loaded but class does not exist');
            }
        }
        return true;
    }

    public function addModule($frontName, $moduleName, $routeName, $codepool = 'core')
    {
        $this->_modules[$frontName] = $moduleName;
        $this->_routes[$routeName] = $frontName;
        foreach($moduleName as $moduleRealName) {
            App_Main::getConfig()->setData('codepool-'. $moduleRealName, $codepool);
        }
        return $this;
    }

    public function getModuleByFrontName($frontName)
    {
        if (isset($this->_modules[$frontName])) {
            return $this->_modules[$frontName];
        }
        return false;
    }

    public function getModuleByName($moduleName, $modules)
    {
        foreach ($modules as $module) {
            if ($moduleName === $module || (is_array($module)
                    && $this->getModuleByName($moduleName, $module))) {
                return true;
            }
        }
        return false;
    }

    public function getFrontNameByRoute($routeName)
    {
        if (isset($this->_routes[$routeName])) {
            return $this->_routes[$routeName];
        }
        return false;
    }

    public function getRouteByFrontName($frontName)
    {
        return array_search($frontName, $this->_routes);
    }

    public function validateControllerFileName($fileName)
    {
        if ($fileName && is_readable($fileName) && false===strpos($fileName, '//')) {
            return true;
        }
        return false;
    }

    public function getControllerClassName($realModule, $controller)
    {
        $class = uc_words($realModule).'_Controller_'.uc_words($controller).'Controller';
        return $class;
    }

    protected function _checkShouldBeSecure($request, $path='')
    {
        //return true;
        if ($this->_shouldBeSecure($path) && !App_Main::isCurrentlySecure()) {
             $url = $this->_getCurrentSecureUrl($request);

            App_Main::getResponse()
                ->setRedirect($url)
                ->sendResponse();
            exit;
        }
    }

    protected function _getCurrentSecureUrl($request)
    {
        return App_Main::getBaseUrl('link', true).ltrim($request->getPathInfo(), '/');
    }

    protected function _shouldBeSecure($path)
    {
        return substr(App_Main::getConfig('web/unsecure/base_url'),0,5)==='https' || App_Main::getConfig('web/secure/use_in_frontend')
            && substr(App_Main::getConfig('web/secure/base_url'),0,5)=='https' && App_Main::getConfig()->shouldUrlBeSecure($path);
    }
}