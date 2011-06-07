<?php
/**
 * class Core_Controller_Response_Http
 * 
 * @package Core
 * @category Controller-Response
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Controller_Response_Http extends Zend_Controller_Response_Http
{

    /**
     * Fixes CGI only one Status header allowed bug
     *
     * @link  http://bugs.php.net/bug.php?id=36705
     *
     */
    public function sendHeaders()
    {
        if (!$this->canSendHeaders()) {
            App_Main::log('HEADERS ALREADY SENT: ');
            return $this;
        }

        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
            $statusSent = false;
            foreach ($this->_headersRaw as $i=>$header) {
                if (stripos($header, 'status:')===0) {
                    if ($statusSent) {
                        unset($this->_headersRaw[$i]);
                    } else {
                        $statusSent = true;
                    }
                }
            }
            foreach ($this->_headers as $i=>$header) {
                if (strcasecmp($header['name'], 'status')===0) {
                    if ($statusSent) {
                        unset($this->_headers[$i]);
                    } else {
                        $statusSent = true;
                    }
                }
            }
        }
        parent::sendHeaders();
    }
}