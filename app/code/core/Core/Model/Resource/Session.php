<?php
/**
 * class Core_Model_Resource_Session
 * Resource model for the session object
 * 
 * @package Core
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Resource_Session extends Core_Model_Resource_Abstract
{
    /**
     * Session lifetime
     *
     * @var integer
     */
    protected $_lifeTime;

    /**
     * Session data table name
     *
     * @var string
     */
    protected $_sessionTable;

    /**
     * Database read connection object
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Database write connection object
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $this->_sessionTable = 'core_session';
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Get the session lifetime, if not set it is collected from the PHP config
     * 
     * @return intiger 
     */
    public function getLifeTime()
    {
        if (is_null($this->_lifeTime)) {
            $this->_lifeTime = ini_get('session.gc_maxlifetime');
            if (!$this->_lifeTime) {
                $this->_lifeTime = 3600;
            }
        }
        return $this->_lifeTime;
    }

    /**
     * Check DB connection
     *
     * @return bool
     */
    public function hasConnection()
    {
        if (!$this->_read) {
            return false;
        }
        $tables = $this->_read->getTables();
        if (empty($tables) || !in_array($this->_sessionTable, $tables)) {
            return false;
        }

        return true;
    }

    /**
     * Set the save handler, enalbes DB based handler only if the session table is found
     * 
     * @return Core_Model_Resource_Session 
     */
    public function setSaveHandler()
    {
        if ($this->hasConnection()) {
            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
            );
        } else {
            session_save_path(App_Main::getBaseDir('session'));
        }
        return $this;
    }

    /**
     * Open session
     *
     * @param string $savePath ignored
     * @param string $sessName ignored
     * @return boolean
     */
    public function open($savePath, $sessName)
    {
        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        $this->gc($this->getLifeTime());

        return true;
    }

    /**
     * Fetch session data
     *
     * @param string $sessId
     * @return string
     */
    public function read($sessId)
    {
        $data = $this->_read->fetchOne("SELECT session_data FROM ". $this->_sessionTable ." WHERE session_id = '". $sessId ."' AND session_expires > ".time());
        return $data;
    }

    /**
     * Update session
     *
     * @param string $sessId
     * @param string $sessData
     * @return boolean
     */
    public function write($sessId, $sessData)
    {
        $data = array(
            'session_id' => $this->_prepareValueForSave($sessId),
            'session_expires'=>time() + $this->getLifeTime(),
            'session_data'=> $this->_prepareValueForSave($sessData)
        );

        $exists = $this->_write->fetchOne("SELECT session_id FROM `". $this->_sessionTable ."` WHERE session_id = '". $sessId ."'");
        

        if ($exists) {
            $this->_write->update($this->_sessionTable, $data, 'session_id');
        } else {
            $this->_write->insert($this->_sessionTable, $data);
        }

        return true;
    }

    /**
     * Destroy session
     *
     * @param string $sessId
     * @return boolean
     */
    public function destroy($sessId)
    {
        $this->_write->query("DELETE FROM `". $this->_sessionTable ."` WHERE session_id = '". $sessId ."'");
        return true;
    }

    /**
     * Garbage collection
     *
     * @param int $sessMaxLifeTime ignored
     * @return boolean
     */
    public function gc($sessMaxLifeTime)
    {
        $this->_write->query("DELETE FROM `". $this->_sessionTable ."` WHERE `session_expires` < ". time());
        return true;
    }
}
?>