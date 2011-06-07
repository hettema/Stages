<?php
/**
 * class Core_Controller_Router_Admin
 * Admin router for backend module and request management
 * 
 * @package Core
 * @category Controller-Router
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Router_Admin extends Core_Controller_Router_Standard
{

    public function collectRoutes($configArea, $useRouterName)
    {
        $routersConfigModules = array(
                                      array('frontName'=>'admin', 'routeName'=>'admin','module'=>'Admin'),
                                      array('frontName'=>'backend', 'routeName'=>'backend','module'=>'Backend'),
                                      
                                     );
        if(empty($routersConfigModules)) { return $this; }
        $routersModules = $routersConfigModules;

        foreach($routersModules as $module) {
            $this->addModule($module['frontName'], array($module['module']), $module['routeName']);
        }

        return $this;
    }
    
    /**
     * Fetch the default routes
     */
    public function fetchDefault()
    {
        $this->getFront()->setDefault(array(
            'module'     => 'admin',
            'controller' => 'index',
            'action'     => 'index'
        ));
    }

    /**
     * Process before maching the routes
     *
     * @return unknown
     */
    protected function _beforeModuleMatch()
    { 
        if(!$this->validateRoute()) { return false; }
        
        $session  = App_Main::getAdminSession();
        /* @var $session Core_Model_Session */
        $request = App_Main::getRequest();
        $user = $session->getUser();

        if ($request->getActionName() == 'forgotpassword' || $request->getActionName() == 'logout') {
            $request->setDispatched(true);
        }
        else {
            if($user) {
                $user->reload();
            }
            if (!$user || !$user->getId()) {
                if ($request->getPost('login')) {
                    $postLogin  = $request->getPost('login');
                    $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
                    $password   = isset($postLogin['password']) ? $postLogin['password'] : '';
                    $user = $session->login($username, $password, $request);
                    $request->setPost('login', null);
                }
                if (!$request->getParam('forwarded')) {
                    $request->setParam('forwarded', true)
                        ->setRouteName('admin')
                        ->setControllerName('index')
                        ->setActionName('login')
                        ->setDispatched(false);
                    
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * Check for the module existance in the current added routes.
     * If not a page not found result will load the login page instead
     *
     * @return array|bool     *
     */
    private function validateRoute()
    {
        $request = App_Main::getRequest();
        $p = explode('/', trim($request->getPathInfo(), '/'));

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

        return $this->getModuleByFrontName($module);
    }

    /**
     * 
     * @return bool
     */
    protected function _afterModuleMatch()
    {
        return true;
    }

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    protected function _shouldBeSecure($path)
    {
        return App_Main::getConfig('web-secure-backend');
    }

    protected function _getCurrentSecureUrl($request)
    {
        return App_Main::getBaseUrl('link', true).ltrim($request->getPathInfo(), '/');
    }
}