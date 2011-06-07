<?php
/**
 * class Main_Mcrypt
 * Mcrypt wraper object for handling encoding and decoding
 * 
 * @package Core
 * @subpackage Db
 * @category Lib-Object
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Main_Mcrypt extends Core_Model_Object
{
    /**
     * Constuctor
     *
     * @param array $data
     */
    public function __construct(array $data=array())
    {
        parent::__construct($data);
    }

    /**
     * Initialize mcrypt module
     *
     * @param string $key cipher private key
     * @return Buzrr_Mcrypt
     */
    public function init($key)
    {
        if (!$this->getCipher()) {
            $this->setCipher(MCRYPT_BLOWFISH);
        }

        if (!$this->getMode()) {
            $this->setMode(MCRYPT_MODE_ECB);
        }

        $this->setHandler(mcrypt_module_open($this->getCipher(), '', $this->getMode(), ''));
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($this->getHandler()), MCRYPT_RAND);

        $maxKeySize = mcrypt_enc_get_key_size($this->getHandler());

        if (iconv_strlen($key, 'UTF-8')>$maxKeySize) {
            $this->setHandler(null);
            throw new Zend_Exception('Maximum key size must should be smaller '.$maxKeySize);
        }

        mcrypt_generic_init($this->getHandler(), $key, $iv);

        return $this;
    }

    /**
     * Encrypt data
     *
     * @param string $data source string
     * @return string
     */
    public function encrypt($data)
    {
        if (!$this->getHandler()) {
            throw new Zend_Exception('Crypt module is not initialized.');
        }
        if (strlen($data) == 0) {
            return $data;
        }
        return mcrypt_generic($this->getHandler(), $data);
    }

    /**
     * Decrypt data
     *
     * @param string $data encrypted string
     * @return string
     */
    public function decrypt($data)
    {
        if (!$this->getHandler()) {
            throw new Zend_Exception('Crypt module is not initialized.');
        }
        if (strlen($data) == 0) {
            return $data;
        }
        return mdecrypt_generic($this->getHandler(), $data);
    }

    /**
     * Desctruct cipher module
     *
     */
    public function __destruct()
    {
        if ($this->getHandler()) {
            $this->_reset();
        }
    }

    protected function _reset()
    {
        mcrypt_generic_deinit($this->getHandler());
        mcrypt_module_close($this->getHandler());
    }
}
?>