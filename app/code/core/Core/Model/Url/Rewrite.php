<?php
/**
 * class Core_Model_Url_Rewrite
 * Object to handle db based url rewrite
 * 
 * @package Core
 * @subpackage Url
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Url_Rewrite extends Core_Model_Abstract
{
    const REWRITE_REQUEST_PATH_ALIAS = 'rewrite_request_path';

    protected function _construct()
    {
        $this->_init('core/url_rewrite');
    }

    /**
     * Load rewrite information for request
     *
     * if $path is array - that mean what we need try load for each item
     *
     * @param   mixed $path
     * @return  Core_Model_Url_Rewrite
     */
    public function loadByRequestPath($path)
    {
        $this->setId(null);

        if (is_array($path)) {
            foreach ($path as $pathInfo) {
                $this->load($pathInfo, 'request_path');
                if ($this->getId()) {
                    return $this;
                }
            }
        }
        else {
            $this->load($path, 'request_path');
        }
        return $this;
    }

    /**
     * Load rewrite info using the idpath
     * 
     * @param type $path
     * @return Core_Model_Url_Rewrite 
     */
    public function loadByIdPath($path)
    {
        $this->setId(null)->load($path, 'id_path');
        return $this;
    }

    /**
     * 
     * @param type $key
     * @return bool 
     */
    public function hasOption($key)
    {
        $optArr = explode(',', $this->getOptions());

        return array_search($key, $optArr) !== false;
    }
    
    /**
     * Logic of custom rewrites
     *
     * @param   Zend_Controller_Request_Http $request
     * @param   Zend_Controller_Response_Http $response
     * @return  Core_Model_Url
     */
    public function rewrite(Zend_Controller_Request_Http $request=null, Zend_Controller_Response_Http $response=null)
    {
        if (is_null($request)) {
            $request = App_Main::getRequest();
        }
        if (is_null($response)) {
            $response = App_Main::getResponse();
        }

        $requestCases = array();
        $requestPath = trim($request->getPathInfo(), '/');

        /**
         * We need try to find rewrites information for both cases
         * More priority has url with query params
         */
        if ($queryString = $this->_getQueryString()) {
            $requestCases[] = $requestPath .'?'.$queryString;
            $requestCases[] = $requestPath;
        }
        else {
            $requestCases[] = $requestPath;
        }

        $this->loadByRequestPath($requestCases);

        /**
         * Try to find rewrite by request path at first, if no luck - try to find by id_path
         */

        if (!$this->getId()) {
            return false;
        }


        $request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
        $external = substr($this->getTargetPath(), 0, 6);
        $isPermanentRedirectOption = $this->hasOption('RP');
        if ($external === 'http:/' || $external === 'https:') {
            if ($isPermanentRedirectOption) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            header("Location: ".$this->getTargetPath());
            exit;
        } else {
            $targetUrl = $request->getBaseUrl(). '/' . $this->getTargetPath();
        }
        $isRedirectOption = $this->hasOption('R');
        if ($isRedirectOption || $isPermanentRedirectOption) {
            $targetUrl = $request->getBaseUrl() . '/' .$this->getTargetPath();

            if ($isPermanentRedirectOption) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            header('Location: '.$targetUrl);
            exit;
        }

        $targetUrl = $request->getBaseUrl(). '/' .$this->getTargetPath();

        if ($queryString = $this->_getQueryString()) {
            $targetUrl .= '?'.$queryString;
        }

        $request->setRequestUri($targetUrl);
        $request->setPathInfo($this->getTargetPath());

        return true;
    }

    /**
     * Get the query string for the current request
     * 
     * @return string query string 
     */
    protected function _getQueryString()
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queryParams = array();
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $hasChanges = false;
            foreach ($queryParams as $key=>$value) {
                if (substr($key, 0, 3) === '___') {
                    unset($queryParams[$key]);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                return http_build_query($queryParams);
            }
            else {
                return $_SERVER['QUERY_STRING'];
            }
        }
        return false;
    }

    /**
     * Create a new url rewite information
     *
     * @param Core_Model_Abstract $object
     * @param string $idpath
     * @param string $requestPath
     * @param string $targetPath
     * @return Core_Model_Url_Rewrite 
     */
    public function newUrlRewite(Core_Model_Abstract $object, $idpath = null, $requestPath = null, $targetPath = null)
    {
        if(empty($requestPath)) {
            if(!$object->getRequestPath()) {

                if($object->getRequestPathPrepend()) {
                    $requestPath .= trim($object->getRequestPathPrepend(), '/') .'/';
                }
                if($object->getUrlKey()) {
                    $requestPath .= $object->getUrlKey();
                } else {
                    $requestPath  .= $object->getId();
                }
            } else {
                $requestPath = $object->getRequestPath();
            }
        }
        $data['request_path'] = mb_strtolower($requestPath, 'UTF-8');


        if(empty($targetPath)) {
            if(!$object->getTargetPath()) {

                if($object->getTargetPathPrepend()) {
                    $targetPath .= trim($object->getTargetPathPrepend(), '/') .'/';
                }
                $targetPath .= $object->getId();
            } else {
                $targetPath = $object->getTargetPath();
            }
        }
        $data['target_path'] = $targetPath;

        if(empty ($idpath)) {
            if(!$object->getIdPath()) {

                if($object->getIdPathPrepend()) {
                    $idpath .= rtrim($object->getIdPathPrepend(), '/') .'/';
                }
                $idpath .= $object->getId();
            } else {
                $idpath = $object->getIdPath();
            }
        }
        $data['id_path'] = $idpath;

        $this->setData($data);

        
        $this->loadByIdPath($data['id_path']);
        if(!$this->getId()) {
            $this->loadByRequestPath($data['request_path']);
        }

        $this->save();
        return $this;
    }

    /**
     * Update the exisitng url rewrite data
     *
     * @param string $idPath
     * @param string $requestPath
     * @return Core_Model_Url_Rewrite 
     */
    public function updateUrlRewite($idPath, $requestPath)
    {
        $this->_getResource()->updateUrlRewrite($idPath, $requestPath);
        return $this;
    }

    /**
     * Remove an existing url rewrite by idPath
     *
     * @param string $idPath
     * @return Core_Model_Url_Rewrite 
     */
    public function removeUrlRewrite($idPath)
    {
        $this->_getResource()->removeUrlRewrite($idPath);
        return $this;
    }

}
