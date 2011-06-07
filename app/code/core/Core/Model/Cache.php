<?php
/**
 * class Core_Model_Cache
 * Cache object model to cache the static html content in a block level
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Cache extends Core_Model_Abstract
{
/**
     * Cache tag for all cache data exclude config cache
     */
    const CACHE_TAG = 'CGS';

    /**
    * Use Cache
    */
    protected $_useCache;

    protected $_cache = false;

    /**
     * Use session in URL flag
     */
    protected $_useSessionInUrl = true;

    /**
     * Use session var instead of SID for session in URL
     */
    protected $_useSessionVar = false;

    protected $_isCacheLocked = null;

    protected $_cacheBackend = 'apc';
    protected $_cachePrefix = 'cgs';
    protected $_cacheLifetimeDefault = 7200;

    
    public function __construct() {}

    /**
     * Generate cache id with application specific data
     */
    protected function _getCacheId($id=null)
    {
        if ($id) {
            $id = $this->prepareCacheId($id);
        }
        return $id;
    }

    /**
     * Prepare an identifier for cache id or cache tag
     */
    public function prepareCacheId($id)
    {
        $id = strtoupper($id);
        $id = preg_replace('/([^a-zA-Z0-9_]{1,1})/', '_', $id);
        return $id;
    }

    /**
     * Generate cache tags from cache id
     */
    protected function _getCacheTags($tags=array())
    {
        foreach ($tags as $index=>$value) {
            $tags[$index] = $this->_getCacheId($value);
        }
        return $tags;
    }

    protected function getCacheBackend()
    {
        return $this->_cacheBackend;
    }

    protected function getCachePrefix()
    {
        return $this->_cachePrefix;
    }

    protected function getMemCacheConfig()
    {
        $mecacheConfig = new Core_Object();
        $mecacheConfig->setHost('localhost');
        $mecacheConfig->setPort(1207);
        $mecacheConfig->setPersistent(true);
        
        $mecacheConfig->setCompression(9);
        $mecacheConfig->setCacheDir('/var/tmp');
        $mecacheConfig->setHashedDirectoryLevel(4);
        $mecacheConfig->setHashedDirectoryUmask();
        $mecacheConfig->setFileNamePrefix('CGS');
    }

    protected function getCacheLifetime()
    {
        return $this->_cacheLifetimeDefault;
    }

    /**
     * Get Zend cache object
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        if($this->_cache) {
            return $this->_cache;
        }
        
        $backend = $this->getCacheBackend();
        $cachePrefix = $this->getCachePrefix();
        if (!$cachePrefix) {
            $cachePrefix = md5(App_Main::getBaseDir());
        }
            if (extension_loaded('apc') && ini_get('apc.enabled') && $backend == 'apc') {
                $backend = 'Apc';
                $backendAttributes = array('cache_prefix' => $cachePrefix);
            } elseif (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') && $backend=='eaccelerator') {
                $backend = 'Eaccelerator';
                $backendAttributes = array('cache_prefix' => $cachePrefix);
            } elseif ('memcached' == $backend && extension_loaded('memcache')) {
                $backend = 'Memcached';
                $memcachedConfig = $this->getMemCacheConfig();
                $backendAttributes = array(
                    'compression'               => (bool)$memcachedConfig->getCompression(),
                    'cache_dir'                 => (string)$memcachedConfig->getCacheDir(),
                    'hashed_directory_level'    => (string)$memcachedConfig->getHashedDirectoryLevel(),
                    'hashed_directory_umask'    => (string)$memcachedConfig->getHashedDirectoryUmask(),
                    'file_name_prefix'          => (string)$memcachedConfig->getFileNamePrefix(),
                    'servers'                   => array(),
                );
                foreach ($memcachedConfig->servers->children() as $serverConfig) {
                    $backendAttributes['servers'][] = array(
                        'host'          => (string)$serverConfig->getHost(),
                        'port'          => (string)$serverConfig->getPort(),
                        'persistent'    => (string)$serverConfig->getPersistent(),
                    );
                }
            } else {
                $backend = 'File';
                $backendAttributes = array(
                    'cache_dir'                 => App_Main::getBaseDir('cache'),
                    'hashed_directory_level'    => 1,
                    'hashed_directory_umask'    => 0777,
                    'file_name_prefix'          => 'cgs',
                );
            }
            $lifetime = $this->getCacheLifetime();
            
            $this->_cache = Zend_Cache::factory(
                'Core',
                $backend,
                array(
                    'caching'                   => true,
                    'lifetime'                  => $lifetime,
                    'automatic_cleaning_factor' => 0,
                ),
                $backendAttributes,
                false,
                false,
                true
            );

        return $this->_cache;
    }

    /**
     * Load cached data by id
     * 
     * @return string cached data
     */
    public function loadCache($id)
    {
        return $this->getCache()->load($this->_getCacheId($id));
    }

    /**
     * Save cache data
     * 
     * @return Core_Model_Cache
     */
    public function saveCache($data, $id, $tags=array(), $lifeTime=false)
    {
        $tags = $this->_getCacheTags($tags);

        $this->getCache()->save((string)$data, $this->_getCacheId($id), $tags, $lifeTime);
        return $this;
    }

    /**
     * Remove cache by id
     * @return Core_Model_Cache
     */
    public function removeCache($id)
    {
        $this->getCache()->remove($this->_getCacheId($id));
        return $this;
    }

    /**
     * Clean cache
     * @param array cache tags
     * @return Core_Model_Cache
     */
    public function cleanCache($tags=array())
    {
        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = array($tags);
            }
            $tags = $this->_getCacheTags($tags);
            $this->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
        } else {
            $this->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array(self::CACHE_TAG));
        }
        return $this;
    }

    /**
    * Check whether to use caching is enabled for specific component
    *
    * @param string type
    * Components:
    * - config
    * - layout
    * - eav
    * - translate
    *
    * @return boolean
    */
    public function useCache($type=null)
    {
        if (is_null($this->_useCache)) {
            $data = App_Main::useCache($type);
            $this->_useCache = !empty($data) ? (array)$data : array();
        }
        if (empty($type)) {
            return $this->_useCache;
        } else {
            return isset($this->_useCache[$type]) ? (bool)$this->_useCache[$type] : false;
        }
    }
}
?>