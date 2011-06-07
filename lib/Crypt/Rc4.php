<?php
class Crypt_Rc4
{
    protected $i;
    protected $j;
    protected $s;
    protected $t;

    /**
    * Assign encryption key to class
    *
    * @param  string key	- Key which will be used for encryption
    * @return void
    * @access public
    */
    public function key($key)
    {
        $len= strlen($key);
        for ($this->i = 0; $this->i < 256; $this->i++) {
            $this->s[$this->i] = $this->i;
        }

        $this->j = 0;
        for ($this->i = 0; $this->i < 256; $this->i++) {
            $this->j = ($this->j + $this->s[$this->i] + ord($key[$this->i % $len])) % 256;
            $t = $this->s[$this->i];
            $this->s[$this->i] = $this->s[$this->j];
            $this->s[$this->j] = $t;
        }
        $this->i = $this->j = 0;
        return $this;
    }

    /**
    * Encrypt function
    *
    * @param  string paramstr 	- string that will encrypted
    * @return void
    * @access public
    */
    public function crypt($paramstr, $key = '')
    {
        $this->key($key);

        $len= strlen($paramstr);
        for ($c= 0; $c < $len; $c++) {
            $this->i = ($this->i + 1) % 256;
            $this->j = ($this->j + $this->s[$this->i]) % 256;
            $t = $this->s[$this->i];
            $this->s[$this->i] = $this->s[$this->j];
            $this->s[$this->j] = $t;

            $t = ($this->s[$this->i] + $this->s[$this->j]) % 256;

            $paramstr[$c] = chr(ord($paramstr[$c]) ^ $this->s[$t]);
        }
        return $paramstr;
    }

    /**
    * Decrypt function
    *
    * @param  string paramstr 	- string that will decrypted
    * @return void
    * @access public
    */
    public function decrypt($paramstr, $key = '')
    {
        //Decrypt is exactly the same as encrypting the string. Reuse (en)crypt code
        return $this->crypt($paramstr, $key);
    }

}

?>
