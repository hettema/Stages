<?php
/**
 * class Core_Helper_Http 
 * 
 * @package Core
 * @category Helper
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Helper_Http extends Core_Helper_Abstract
{
    const REMOTE_ADDR_HEADERS  = false;

    /**
     * Remote address cache
     *
     * @var string
     */
    protected $_remoteAddr;

    /**
     * Validate and retrieve user and password from HTTP
     *
     * @return array
     */
    public function authValidate($headers = null)
    {
        if(!is_null($headers)) {
            $_SERVER = $headers;
        }

        $user = '';
        $pass = '';

        // moshe's fix for CGI
        if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
            foreach ($_SERVER as $k=>$v) {
                if (substr($k, -18)==='HTTP_AUTHORIZATION' && !empty($v)) {
                    $_SERVER['HTTP_AUTHORIZATION'] = $v;
                    break;
                }
            }
        }

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pass = $_SERVER['PHP_AUTH_PW'];
        }
        //  IIS Note::  For HTTP Authentication to work with IIS,
        // the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
        elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        }
        elseif (!empty($_SERVER['Authorization'])) {
            $auth = $_SERVER['Authorization'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        }

        if (!$user || !$pass) {
            $this->authFailed();
        }

        return array($user, $pass);
    }

    /**
     * Send auth failed Headers and exit
     *
     */
    public function authFailed()
    {
        App_Main::getResponse()
            ->setHeader('HTTP/1.1','401 Unauthorized')
            ->setHeader('WWW-Authenticate','Basic realm="RSS Feeds"')
            ->setBody('<h1>401 Unauthorized</h1>')
            ->sendResponse();
        exit;
    }

    /**
     * Retrieve Remote Addresses Additional check Headers
     *
     * @return array
     */
    public function getRemoteAddrHeaders()
    {
        $headers = array();
        $elements = explode(',', self::REMOTE_ADDR_HEADERS);
        foreach ($elements as $element) {
            $headers[] = $element;
        }

        return $headers;
    }

    /**
     * Retrieve Client Remote Address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getRemoteAddr($ipToLong = false)
    {
        if (is_null($this->_remoteAddr)) {
            $headers = $this->getRemoteAddrHeaders();
            foreach ($headers as $var) {
                if ($this->_getRequest()->getServer($var, false)) {
                    $this->_remoteAddr = $_SERVER[$var];
                    break;
                }
            }

            if (!$this->_remoteAddr) {
                $this->_remoteAddr = $this->_getRequest()->getServer('REMOTE_ADDR');
            }
        }

        if (!$this->_remoteAddr) {
            return false;
        }

        return $ipToLong ? ip2long($this->_remoteAddr) : $this->_remoteAddr;
    }

    /**
     * Retrieve Server IP address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getServerAddr($ipToLong = false)
    {
        $address = $this->_getRequest()->getServer('SERVER_ADDR');
        if (!$address) {
            return false;
        }
        return $ipToLong ? ip2long($address) : $address;
    }

    /**
     * Retrieve HTTP "clean" value
     *
     * @param string $var
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    protected function _getHttpCleanValue($var, $clean = true)
    {
        $value = $this->_getRequest()->getServer($var, '');
        if ($clean) {
            $value = App_Main::getHelper('core/string')->cleanString($value);
        }

        return $value;
    }

    /**
     * Retrieve HTTP HOST
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpHost($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_HOST', $clean);
    }

    /**
     * Retrieve HTTP USER AGENT
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpUserAgent($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_USER_AGENT', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT LANGUAGE
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptLanguage($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_LANGUAGE', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT CHARSET
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptCharset($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_CHARSET', $clean);
    }

    /**
     * Retrieve HTTP REFERER
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpReferer($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_REFERER', $clean);
    }

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getRequestUri($clean = false)
    {
        $uri = $this->_getRequest()->getRequestUri();
        if ($clean) {
            $uri = App_Main::getHelper('core/string')->cleanString($uri);
        }
        return $uri;
    }

    /**
     * Validate IP address
     *
     * @param string $address
     * @return boolean
     */
    public function validateIpAddr($address)
    {
        return preg_match('#^(1?\d{1,2}|2([0-4]\d|5[0-5]))(\.(1?\d{1,2}|2([0-4]\d|5[0-5]))){3}$#', $address);
    }

    /**
     * Get the location detail of the visitor using the ipaddres
     * 
     * @param string $address
     * @return Core_Model_Object
     */
    public function getRemoteLocationDetails($address = null)
    {
        if(!$address) {
            $address = $this->getRemoteAddr();
        }
        $lookUpUrl = 'http://ipinfodb.com/ip_query2.php';
        $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $lookUpUrl . '?ip=' . $address . '&output=xml');
            curl_setopt ($ch, CURLOPT_FAILONERROR, TRUE);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt ($ch, CURLOPT_TIMEOUT, 4);

        $response = curl_exec($ch);

        if(curl_errno($ch))
        {
            $error = 'cURL error: ' . curl_error($ch);
            return false;
        }

        curl_close($ch);

        $ipLocationXml = new SimpleXMLElement($response);
        $data = array();
        foreach($ipLocationXml->Location as $location) {
            if((string)$location->Status != 'OK') continue;
            $data['country_name'] = (string)$location->CountryName;
            $data['country_code'] = (string)$location->CountryCode;
            $data['region_name'] = (string)$location->RegionName;
            $data['region_code'] = (int)$location->RegionCode;
            $data['city'] = (string)$location->City;
            $data['latitude'] = (float)$location->Latitude;
            $data['longitude'] = (float)$location->Longitude;
        }

        $location = new Core_Model_Object($data);
        return $location;
    }
}
