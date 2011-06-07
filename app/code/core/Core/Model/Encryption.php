<?php
/**
 * class Core_Model_Encryption
 * Handles the encryption mechanisms
 * 
 * @package Core
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Encryption
{

    protected $_crypt;
    /**
     * @var Core_Helper_Data
     */
    protected $_helper;

    /**
     * Set helper instance
     *
     * @param Core_Helper_Data $helper
     * @return Core_Model_Encryption
     */
    public function setHelper($helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Generate a [salted] hash.
     *
     * $salt can be:
     * false - a random will be generated
     * integer - a random with specified length will be generated
     * string
     *
     * @param string $password
     * @param mixed $salt
     * @return string
     */
    public function getHash($password, $salt = false)
    {
        if (is_integer($salt)) {
            $salt = $this->_helper->getRandomString($salt);
        }
        return $salt === false ? $this->hash($password) : $this->hash($salt . $password) . ':' . $salt;
    }

    /**
     * Hash a string
     *
     * @param string $data
     * @return string
     */
    public function hash($data)
    {
        return md5($data);
    }

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     * @throws Exception
     */
    public function validateHash($password, $hash)
    {
        $hashArr = explode(':', $hash);
        switch (count($hashArr)) {
            case 1:
                return $this->hash($password) === $hash;
            case 2:
                return $this->hash($hashArr[1] . $password) === $hashArr[0];
        }
        App_Main::throwException('Invalid hash.');
    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return base64_encode($this->_getCrypt()->encrypt((string)$data));
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return str_replace("\x0", '', trim($this->_getCrypt()->decrypt(base64_decode((string)$data))));
    }

    /**
     * Return crypt model, instantiate if it is empty
     *
     * @param string $key
     * @return Crypt_Mcrypt
     */
    public function validateKey($key)
    {
        return $this->_getCrypt($key);
    }
    
    /**
     * Instantiate crypt model
     *
     * @param string $key
     * @return Crypt_Mcrypt
     */
    protected function _getCrypt($key = null)
    {
        if (!$this->_crypt) {
            $crypt = new Mcrypt();
            $this->_crypt = $crypt->init($key);
        }
        return $this->_crypt;
    }
}
