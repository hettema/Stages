<?php
/**
 * Main app object
 * 
 * @package Core
 * @category Main
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));
ini_set('display_errors', 1);
ini_set("default_charset", App_Main::CHARSET);
date_default_timezone_set(App_Main::DEFAULT_TIMEZONE);

// Set include path
App_Main::register('original_include_path', get_include_path());
// Set the application level include path
$paths[] = BP . DS . 'app' . DS . 'code' . DS .'local';
$paths[] = BP . DS . 'app' . DS . 'code' . DS .'core';
$paths[] = BP . DS . 'lib';
$app_path = implode(PS, $paths);
set_include_path($app_path . PS . App_Main::registry('original_include_path'));

// Load config file
include('etc' . DS . 'config.php');
//Load common functions and class autoloader
include('Core' . DS . 'functions.php');

/**
 * class App_Main
 * Public static methods for the application
 * 
 * @package Core
 * @category Static-Object
 */
final class App_Main
{
    
    const SESSION_NAMESPACE = 'dracoo_session';
    const SESSION_ADMIN_LIFETIME = 3600;
    const DEFAULT_TITLE = 'Stages';
    const TITLE_PREFIX = '';
    const TITLE_SUFFIX = ' | Stages';
    const DEFAULT_LANGUAGE = 'en';
    const DEFAULT_LOCALE = 'en_IN';
    const DEFAULT_COUNTRY = 'IN';
    const DEFAULT_REGION = 28;
    const DEFAULT_TIMEZONE = 'UTC';
    const CHARSET = 'UTF-8';
    const MEDIA_TYPE = 'text/html';
    const LOGO_ALT = 'Stages App';
    const DEFAULT_MAIL_SENDER = 'no-replay@stagesapp.com';
    const DEFAULT_MAIL_SENDER_NAME = 'Mail Robot';
    const LOG_VISITOR_INFO = true;
    const DEV_LOG_EXCEPTION_FILE = 'exception.log';

    static private $_registry = array();
    static private $_objects;
    static private $_cacheHtmlBlocks = true;
    static public $_isDeveloperMode = false;

    public static function reset()
    {
        self::$_registry = array();
        self::$_objects  = null;
    }

    /**
     * Register a new variable
     *
     * @param string $key
     * @param mixed $value
     * @param bool $graceful
     */
    public static function register($key, $value, $graceful = false)
    {
        if(isset(self::$_registry[$key])) {
            if ($graceful) {
                return;
            }
        }
        self::$_registry[$key] = $value;
    }

    /**
     * Unregister an entry from registery
     * 
     * @param string $key 
     */
    public static function unregister($key)
    {
        if (isset(self::$_registry[$key])) {
            if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key],'__destruct'))) {
                self::$_registry[$key]->__destruct();
            }
            unset(self::$_registry[$key]);
        }
    }

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed
     */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }
        return null;
    }

    /**
     * Run the application
     * Called from the index.php from the root directory
     */
    public static function run()
    {
        //self::getDbAdapter()->startQueryLogging();
        
        if(!empty($_SESSION['messages'])) {
            self::register('messages', $_SESSION['messages']);
            $_SESSION['messages'] = false;
        }
        
        self::beforeLoadingFrontend();
        self::getControllerFrontend()->dispatch();
        //self::getDbAdapter()->stopQueryLogging();
    }

    /**
     * Start running the aplication in cron job mode
     */
    public static function runCrontab()
    {
        $_cronTab = self::getSingleton('cron/cron');
        self::getTranslator()->init('frontend');
        $_cronTab->initCronJobs();
        $_cronTab->dispatch();
    }

    /**
     * Get application root absolute path
     *
     * @param $type
     *  - base
     *  - script
     *  - media
     *  - etc
     *  - code
     *  - lib
     *  - design
     *  - var
     *  - cache
     *  - session
     *  - template
     *  - locale
     * @return string
     */
    public static function getBaseDir($type='base')
    {
        switch($type)
        {
            case 'base':
                return BP;
            break;        
            case 'script':
                return BP . DS . 'js';
            break;
            case 'media':
                return BP . DS . 'media';
            break;
            case 'etc':
                return BP . DS . 'app' . DS . 'etc';
            break;
            case 'code':
                return BP . DS . 'app' . DS . 'code';
            break;
            case 'lib':
                return BP . DS . 'lib';
            break;
            case 'design':
                return BP . DS . 'design';
            break;
            case 'var' :
                return BP . DS . 'var';
            break;
            case 'cache' :
                return BP . DS . 'var' . DS . 'cache';
            break;
            case 'session' :
                return BP . DS . 'var' . DS . 'session';
            break;
            case 'template' :
                return BP . DS . 'app' . DS . 'template';
            break;
            case 'locale' :
                return BP . DS . 'app' . DS . 'locale';
            break;
        }
        return BP;
    }

    /**
     * Get the module directory for the $moduleName
     *
     * @param string $type
     *  - etc
     *  - controller
     * @param string $moduleName
     * @return string module directory 
     */
    public static function getModuleDir($type, $moduleName)
    {
        $codepool = self::getConfig()->getConfigData('codepool-'. $moduleName);
        $codepool = !$codepool ? 'core' : $codepool;
        $dir = self::getBaseDir('code') . DS . $codepool . DS . uc_words($moduleName, DS);

        switch ($type) {
            case 'etc':
                $dir .= DS.'etc';
            break;

            case 'controller':
                $dir .= DS.'Controller';
                break;
        }

        $dir = str_replace('/', DS, $dir);
        return $dir;
    }

    /**
     * Get model object
     *
     * @link    Core_Model_Config::getModelInstance
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Core_Model_Abstract
     */
    public static function getModel($modelClass='', $arguments=array())
    {
        return self::getModelInstance($modelClass, $arguments);
    }

    /**
     * Check whether the model is already initialised and registered
     * @param string $modelClass
     * @return bool 
     */
    public static function hasSingleton($modelClass='')
    {
        return self::registry('_singleton/'.$modelClass);
    }

    /**
     * Get model object initialized and registered in the application registery
     *
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Core_Model_Abstract
     */
    public static function getSingleton($modelClass='', array $arguments=array())
    {
        $registryKey = '_singleton/'.$modelClass;
        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getModel($modelClass, $arguments));
        }
        return self::registry($registryKey);
    }

    /**
     * Get the model instance for the $modelClass
     * 
     * @param string $modelClass
     * @param array $constructArguments
     * @return className 
     */
    public static function getModelInstance($modelClass='', $constructArguments=array())
    {
        $modelClass = trim($modelClass);
        $className = (strpos($modelClass, '/')===false) ? $modelClass : self::getGroupedClassName('model', $modelClass);
        if (class_exists($className)) {
            $obj = new $className($constructArguments);
            return $obj;
        } else { return false; }
    }

    /**
     * Get the model class name for the $modelType
     * 
     * @param string $modelType
     * @return string class name 
     */
    public static function getModelClassName($modelType)
    {
        if (strpos($modelType, '/')===false) {
            return $modelType;
        }
        return self::getGroupedClassName('model', $modelType);
    }

    /**
     * Get the resource model for the $modelClass
     *
     * @param string $modelClass
     * @param array $arguments
     * @return type 
     */
    public static function getResourceModel($modelClass, $arguments=array())
    {
        if(strpos($modelClass, '/')) {
            $modelClass = self::getGroupedClassName('model_resource', $modelClass);
        }
        return self::getModelInstance($modelClass, $arguments);
    }

    /**
     * Get the resource model initialized and registered in the application registery
     * @param type $modelClass
     * @param array $arguments
     * @return type 
     */
    public static function getResourceSingleton($modelClass='', array $arguments=array())
    {
        $registryKey = '_resource_singleton/'.$modelClass;
        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getResourceModel($modelClass, $arguments));
        }
        return self::registry($registryKey);
    }

    /**
     * Get the controller instance using the $controllerClass
     * 
     * @param string $controllerClass
     * @param array $constructArguments
     * @return className 
     */
    public static function getControllerInstance($controllerClass='', $constructArguments=array())
    {
        $controllerClass = trim($controllerClass);
        $controllerClass .= 'Controller';
        $className = (strpos($controllerClass, '/')===false) ? $controllerClass : self::getGroupedClassName('controller', $controllerClass);
        if (class_exists($className)) {
            $obj = new $className(self::getRequest(), self::getResponse());
            return $obj;
        } else { return false; }
    }

    /**
     * Get the block instance for the block class
     *
     * @param string $blockClass
     * @param array $constructArguments
     * @return className 
     */
    public static function getBlockInstance($blockClass='', $constructArguments=array())
    {
        $blockClass = trim($blockClass);
        $className = (strpos($blockClass, '/')===false) ? $blockClass : self::getGroupedClassName('block', $blockClass);
        if (class_exists($className)) {
            $obj = new $className($constructArguments);
            return $obj;
        } else { return false; }
    }

    /**
     * Get the block class name for the $blockType
     * 
     * @param string $blockType
     * @return string class name 
     */
    public static function getBlockClassName($blockType)
    {
        if (strpos($blockType, '/')===false) {
            return $blockType;
        }
        return self::getGroupedClassName('block', $blockType);
    }

    /**
     * Get the helper object initialized and registered
     * 
     * @param string $name
     * @return Core_Helper_Abstract 
     */
    public static function getHelper($name)
    {
        $registryKey = '_helper_singleton/'.$name;
        if (!self::registry($registryKey)) {
            $class = self::getHelperClassName($name);
            self::register($registryKey, new $class());
        }
        return self::registry($registryKey);
    }

    /**
     * Get the helper class name
     *
     * @param string $helperName
     * @return string class name 
     */
    public static function getHelperClassName($helperName)
    {
        if (strpos($helperName, '/') === false) {
            $helperName .= '/data';
        }
        return self::getGroupedClassName('helper', $helperName);
    }
    
    /**
     * Get the grouped class name from the $groupType and $classId
     * $groupType : - model|block|helper
     * $classId : module/class_name
     * 
     * @param string $groupType
     * @param string $classId
     * @return string class name 
     */
    public static function getGroupedClassName($groupType, $classId)
    {
        $classArr = explode('/', trim($classId));
        $class = !empty($classArr[1]) ? $classArr[1] : null;

        $className = $classArr[0].'_'.$groupType.'_'.$class;
        $className = uc_words($className);
        return $className;
    }
    
    /**
     * Get the MySQL database adapter
     *
     * @return Main_Mysql 
     */
    public static function getDbAdapter()
    {
       $registryKey = '_resource/mysql_adapter';
        if(!self::registry($registryKey)) {
            $_request = new Main_Mysql();
            self::register($registryKey, $_request);
        }
        return self::registry($registryKey);
    }

    /**
     * Get the global request object
     *
     * @return Core_Controller_Request_Http  
     */
    public static function getRequest()
    {
       $registryKey = '_controller/core_request';
        if(!self::registry($registryKey)) {
            $_request = new Core_Controller_Request_Http();
            self::register($registryKey, $_request);
        }
        return self::registry($registryKey);
    }

    /**
     * Get the global response object
     *
     * @return Core_Controller_Response_Http
     */
    public static function getResponse()
    {        
        $registryKey = '_controller/core_response';
        if(!self::registry($registryKey)) {
            $_response = new Core_Controller_Response_Http();
            $_response->setHeader("Content-Type", "text/html; charset=UTF-8");
            self::register($registryKey, $_response);
        }
        return self::registry($registryKey);
    }

    /**
     * Get the core session object
     *
     * @return Core_Model_Session 
     */
    public static function getSession()
    {
        return self::getSingleton('core/session', array(false));
    }

    /**
     * Get the admin session object
     * 
     * @return Admin_Model_Session 
     */
    public static function getAdminSession()
    {
        return self::getSingleton('admin/session', array(false));
    }

    /**
     * Get the translator model
     *
     * @return Core_Model_Translate 
     */
    public static function getTranslator()
    {
        return self::getSingleton('core/translate');
    }

    /**
     * Get the core website for the current request
     * 
     * @return Core_Model_Website 
     */
    public static function getWebsite()
    {
        return self::getSingleton('core/website');
    }

    /**
     * Get the application config or the config value for $path 
     *
     * @param string $path
     * @return mixed 
     */
    public static function getConfig($path = null)
    {
        if($path) {
            return self::getSingleton('core/config')->getConfigData($path);
        }
        return self::getSingleton('core/config');
    }

    /**
     * Process before loading the frontend
     */
    public static function beforeLoadingFrontend()
    {
    }
    
    /**
     *
     * @return Core_Controller_Frontend 
     */
    public static function getControllerFrontend()
    {
        $registryKey = '_controller/core_front';
        if(!self::registry($registryKey)) {
            $_front = new Core_Controller_Frontend();
            self::register($registryKey, $_front);
        }
        return self::registry($registryKey);
    }

    /**
     *
     * @return Core_Model_Layout 
     */
    public static function getLayout()
    {
        return self::getSingleton('core/layout');
    }
    
    /**
     *
     * @return Core_Model_Design 
     */
    public static function getDesign()
    {
        return self::getSingleton('core/design');
    }

    /**
     * Get the log visitor flag
     *
     * @return bool 
     */
    public static function logVisitor()
    {
        return self::LOG_VISITOR_INFO;
    }

    public static function isRouterAdmin()
    {
        return true;
    }

    /**
     * 
     * @return bool 
     */
    public static function isAdmin()
    {
        if(!self::hasSingleton('core/session')) { return false; }
        return self::getSession()->getSessionName() == 'backend';
    }

    public static function getDefaultTitle() { return self::getWebsite()->getConfig('website-default-title') ? self::getWebsite()->getConfig('website-default-title') : self::DEFAULT_TITLE; }
    public static function getDefaultDescription() { return self::getWebsite()->getConfig('website-default-metadesciption'); }
    public static function getDefaultKeywords() { return self::getWebsite()->getConfig('website-default-keywords'); }
    public static function getDefaultRobots() { return 'INDEX/FOLLOW'; }

    /**
     * Check whether the cache is enabled for the 
     * 
     * @param string $type
     * 
     * @return bool 
     */
    public static function useCache($type)
    {
        switch($type)
        {
            case 'block_html':
                return self::$_cacheHtmlBlocks;
            break;
        
            default:
                return false;
            break;
        }
    }

    public static function getCacheFactory()
    {
        return self::getSingleton('core/cache');
    }
       
    /** 
     * General function to mange system messages
     * @param string $type
     * @param string $msgStr
     * @return bool 
     */
    public static function setMessage($type, $msgStr)
    {
        $messages = self::getMessages();
        $messages[$type][] = $msgStr;
        self::register('messages', $messages);
        return true;
    }

    /**
     * Get the stored messages from registery
     * @return array 
     */
    public static function getMessages()
    {
        $messages = self::registry('messages');
        if(!empty($messages)) {
            self::unregister('messages');
        } else {
            $messages = array();
        }
        return $messages;
    }

    /**
     * Get the url biuld using the Core_Model_Url
     *
     * @param string $route
     * @param array $params
     * @return string url 
     */
    public static function getUrl($route='', $params=array())
    {
        if(!isset($params['_secure']) && self::getRequest()->isSecure()) {
            $params['_secure'] = true;
        }
        return self::getModel('core/url')->getUrl($route, $params);
    }

    /**
     * Get the base url for the current/default website
     *
     * @param string $type
     * @param bool $secure
     * @return string base url 
     */
    public static function getBaseUrl($type = 'web', $secure = false)
    {
        return self::getWebsite()->getBaseUrl($type, $secure);
    }

    /**
     * Check whether the current request is secure
     * @return bool 
     */
    public static function isCurrentlySecure()
    {
        return self::getRequest()->isSecure();
    }

    /**
     *
     * @param string $url 
     */
    public static function redirect($url)
    {
        $messages = self::registry('messages');
        if(!empty($messages)) {
            $_SESSION['messages'] = $messages;
        }
        header('Location:' . $url);
    }

    /**
     * Redeclare custom error handler
     *
     * @param   string $handler
     */
    public static function setErrorHandler($handler='coreErrorHandler')
    {
        set_error_handler($handler);
    }
    
    public static function exception($module='Core', $message='', $code=0)
    {
        $className = $module.'_Exception';
        return new $className($message, $code);
    }

    public static function throwException($message, $messageStorage=null)
    {
        if ($messageStorage && ($storage = self::getSingleton($messageStorage))) {
            $storage->addError($message);
        }
        $reportId = self::printException(new Core_Exception($message), '', false);

        if (!headers_sent()) {
            $_response = self::getResponse();
            $_response->setHeader('Status', '503 Service Temporarily Unavailable');
            $_response->setHeader('Retry-After','60');
            ob_start();
                include BP . DS .'error'. DS .'index.php';
                $html = ob_get_clean();
            $_response->setBody($html);
            $_response->sendResponse();
        } else {
            print '<script type="text/javascript">';
            print "window.location.href = '". SERVER_URI ."error/';";
            print '</script>';
        }
        die();
    }
    
    public static function log($message, $level=null, $file = '')
    {
        if (!self::getConfig()) {
            return;
        }
        if (self::getConfig('dev-log-inactive')) { //@#ToDO change it active once the code is there to set it
            return;
        }

        static $loggers = array();

        $level  = is_null($level) ? Zend_Log::DEBUG : $level;
        if (empty($file)) {
            $file = self::getConfig('dev-log-file');
            $file   = empty($file) ? 'system.log' : $file;
        }

        try {
            if (!isset($loggers[$file])) {
                $logFile = self::getBaseDir('var').DS.'log'.DS.$file;
                $logDir = self::getBaseDir('var').DS.'log';

                if (!is_dir(self::getBaseDir('var').DS.'log')) {
                    mkdir(self::getBaseDir('var').DS.'log', 0777);
                }

                if (!file_exists($logFile)) {
                    file_put_contents($logFile,'');
                    chmod($logFile, 0777);
                }

                $format = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
                $formatter = new Zend_Log_Formatter_Simple($format);
                $writer = new Zend_Log_Writer_Stream($logFile);
                $writer->setFormatter($formatter);
                $loggers[$file] = new Zend_Log($writer);
            }

            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }

            $loggers[$file]->log($message, $level);
        } catch (Exception $e){

        }
    }

    public static function logException(Exception $e, $errorAlt = null)
    {
        $message = sprintf(' %s%sException message: %s%sTrace: %s',
            $errorAlt,
            "\n",
            $e->getMessage(),
            "\n",
            $e->getTraceAsString());
        return self::log($message);
    }

    /**
     * Set enabled developer mode
     *
     * @param bool $mode
     * @return bool
     */
    public static function setIsDeveloperMode($mode)
    {
        self::$_isDeveloperMode = (bool)$mode;
        return self::$_isDeveloperMode;
    }

    /**
     * Retrieve enabled developer mode
     *
     * @return bool
     */
    public static function getIsDeveloperMode()
    {
        return self::$_isDeveloperMode;
    }

    /**
     * Display exception
     *
     * @param Exception $e
     */
    public static function printException(Exception $e, $extra = '', $redirect = true)
    {
        if (self::$_isDeveloperMode) {
            print '<pre>';

            if (!empty($extra)) {
                print $extra . "\n\n";
            }

            print $e->getMessage() . "\n\n";
            print $e->getTraceAsString();
            print '</pre>';
        }
        else {
            $_reportDir = self::getBaseDir('var') . DS . 'report';
            if(!file_exists($_reportDir)) {
                mkdir($_reportDir);
            }
            $reportId   = intval(microtime(true) * rand(100, 1000));
            $reportFile = $_reportDir . DS . $reportId;
            $reportData = array(
                !empty($extra) ? $extra . "\n\n" : '' . $e->getMessage(),
                $e->getTraceAsString()
            );
            $reportData = serialize($reportData);

            file_put_contents($reportFile, $reportData);
            chmod($reportFile, 0777);

            if(!$redirect) { return $reportId; }
            
            $reportUrl = rtrim(self::getBaseUrl(), '/') . '/var/report/?id='. $reportId;

            if (!headers_sent()) {
                header('Location: ' . $reportUrl);
            }
            else {
                print '<script type="text/javascript">';
                print "window.location.href = '{$reportUrl}';";
                print '</script>';
            }
        }

        die();
    }
}
?>
