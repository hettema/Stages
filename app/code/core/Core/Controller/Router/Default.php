<?php
/**
 * class Core_Controller_Router_Default
 * Default router initialized for the index action
 * 
 * @package Core
 * @category Controller-Router
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Router_Default extends Core_Controller_Router_Abstract
{
    public function match(Zend_Controller_Request_Http $request)
    {
        $request->setModuleName('cms')
            ->setControllerName('index')
            ->setActionName('noroute');

        return true;
    }

}