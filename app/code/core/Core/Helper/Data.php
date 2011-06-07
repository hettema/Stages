<?php
/**
 * class Core_Helper_Data
 * 
 * @package Core
 * @category Helper
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Helper_Data extends Core_Helper_Abstract
{
    /**
     * @var Core_Model_Encryption
     */
    protected $_encryptor = null;

    /**
     * @return Core_Model_Encryption
     */
    public function getEncryptor()
    {
        if ($this->_encryptor === null) {
           $this->_encryptor = App_Main::getModel('core/encryption');
            $this->_encryptor->setHelper($this);
        }
        return $this->_encryptor;
    }

    /**
     * Encrypt data using application key
     *
     * @param   string $data
     * @return  string
     */
    public function encrypt($data)
    {
        return $this->getEncryptor()->encrypt($data);
    }

    /**
     * Decrypt data using application key
     *
     * @param   string $data
     * @return  string
     */
    public function decrypt($data)
    {
        return $this->getEncryptor()->decrypt($data);
    }

    /**
     * Validate the apllication key
     * 
     * @param string $key
     * @return bool 
     */
    public function validateKey($key)
    {
        return $this->getEncryptor()->validateKey($key);
    }

    /**
     * Encrypt the string with the supplied key
     * 
     * @param string $string
     * @return string 
     */
    public function encrypt_rc4($string, $key = 'cgs')
    {
        $crypter = new Crypt_Rc4();
        return urlencode(base64_encode($crypter->crypt($string, $key)));
    }

    /**
     * Decrypt the string with the supplied key
     * 
     * @param string $string
     * @return string 
     */
    public function decrypt_rc4($string, $key = 'cgs')
    {
        $crypter = new Crypt_Rc4();
        return $crypter->decrypt(base64_decode(urldecode($string)), $key);
    }

    /**
     * Get random string from the supplied charecters or from the default alphaneumerics
     *
     * @param int $len
     * @param string $chars
     * @return string 
     */
    public function getRandomString($len, $chars=null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     * Generate salted hash from password
     *
     * @param string $password
     * @param string|integer|boolean $salt
     */
    public function getHash($password, $salt = false)
    {
        return $this->getEncryptor()->getHash($password, $salt);
    }

    /**
     * Validate the hash with the password
     *
     * @param string $password
     * @param string $hash
     * @return type 
     */
    public function validateHash($password, $hash)
    {
        return $this->getEncryptor()->validateHash($password, $hash);
    }
}