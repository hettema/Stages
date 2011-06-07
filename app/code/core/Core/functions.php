<?php
/**
 * Copyright 2009, 2010 hette.ma.
 *
 * This file is part of Mindspace.
 * Mindspace is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.Mindspace is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.You should have received a copy of the GNU General Public License
 * along with Mindspace. If not, see <http://www.gnu.org/licenses/>.
 *
 *  credits
 * ----------
 * Idea by: Garrett French |    http://ontolo.com    |     garrett <dot> french [at] ontolo (dot) com
 * Code by: Alias Eldhose| http://ceegees.in  | eldhose (at) ceegees [dot] in
 * Initiated by: Dennis Hettema    |    http://hette.ma    |     hettema (at) gmail [dot] com
 * 
 * @package Core
 * @category functions
 */

if (get_magic_quotes_gpc()) {
    function undoMagicQuotes($array, $topLevel=true) {
        $newArray = array();
        foreach($array as $key => $value) {
            if (!$topLevel) {
                $newKey = stripslashes($key);
                if ($newKey!==$key) {
                    unset($array[$key]);
                }
                $key = $newKey;
            }
            $newArray[$key] = is_array($value) ? undoMagicQuotes($value, false) : stripslashes($value);
        }
        return $newArray;
    }
    $_GET = undoMagicQuotes($_GET);
    $_POST = undoMagicQuotes($_POST);
    $_COOKIE = undoMagicQuotes($_COOKIE);
    $_REQUEST = undoMagicQuotes($_REQUEST);
}

/**
 * Get a request query param
 * @param string $key
 * @param bool $gaceful
 * @return type 
 */
function getReqestParam($key, $gaceful = false)
{
    if(isset($_POST[$key])) { $value = $_POST[$key]; }
    if(isset($_GET[$key])) { $value =  $_GET[$key]; }
    if(isset($_REQUEST[$key])) { $value = $_REQUEST[$key]; }

    if(isset($value)) {
        if(strlen($value) > 0) return $value; //value may be set but an empty string
        if($gaceful) return true;
    }
    return false;
}

/**
 * Class autoload
 *
 * @param string $class
 * @return object instance
 */
function __autoload($class)
{
    if (strpos($class, '/')!==false) {
        return;
    }
    $classFile = uc_words($class, DS).'.php';
    include_once($classFile);
}

/**
 * Object destructor
 *
 * @param mixed $object
 */
function destruct($object)
{
    if (is_array($object)) {
        foreach ($object as $obj) {
            destruct($obj);
        }
    } elseif (is_object($object)) {
        if (in_array('__destruct', get_class_methods($object))) {
            $object->__destruct();
        }
    }
    unset($object);
}

/**
 * Enhance the ucword funtion
 *
 * capitalize first letters and convert separators if needed
 *
 * @param string $str
 * @param string $destSep
 * @param string $srcSep
 * @return string
 */
function uc_words($str, $destSep='_', $srcSep='_')
{
    return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
}

/**
 * Get the current date in the format YYYY-MM-DD HH:MM:SS
 * 
 * @return string date today 
 */
function now()
{
    return date('Y-m-d H:i:s');
}

/**
 * Custom error handler
 *
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param integer $errline
 */
function coreErrorHandler($errno, $errstr, $errfile, $errline){
    if (strpos($errstr, 'DateTimeZone::__construct')!==false) {
        // there's no way to distinguish between caught system exceptions and warnings
        return false;
    }

    $errno = $errno & error_reporting();
    if ($errno == 0) {
        return false;
    }
    if (!defined('E_STRICT')) {
        define('E_STRICT', 2048);
    }
    if (!defined('E_RECOVERABLE_ERROR')) {
        define('E_RECOVERABLE_ERROR', 4096);
    }

    // PEAR specific message handling
    if (stripos($errfile.$errstr, 'pear') !== false) {
         // ignore strict notices
        if ($errno == E_STRICT) {
            return false;
        }
        // ignore attempts to read system files when open_basedir is set
        if ($errno == E_WARNING && stripos($errstr, 'open_basedir') !== false) {
            return false;
        }
    }

    $errorMessage = '';

    switch($errno){
        case E_ERROR:
            $errorMessage .= "Error";
            break;
        case E_WARNING:
            $errorMessage .= "Warning";
            break;
        case E_PARSE:
            $errorMessage .= "Parse Error";
            break;
        case E_NOTICE:
            $errorMessage .= "Notice";
            break;
        case E_CORE_ERROR:
            $errorMessage .= "Core Error";
            break;
        case E_CORE_WARNING:
            $errorMessage .= "Core Warning";
            break;
        case E_COMPILE_ERROR:
            $errorMessage .= "Compile Error";
            break;
        case E_COMPILE_WARNING:
            $errorMessage .= "Compile Warning";
            break;
        case E_USER_ERROR:
            $errorMessage .= "User Error";
            break;
        case E_USER_WARNING:
            $errorMessage .= "User Warning";
            break;
        case E_USER_NOTICE:
            $errorMessage .= "User Notice";
            break;
        case E_STRICT:
            $errorMessage .= "Strict Notice";
            break;
        case E_RECOVERABLE_ERROR:
            $errorMessage .= "Recoverable Error";
            break;
        default:
            $errorMessage .= "Unknown error ($errno)";
            break;
    }

    $errorMessage .= ": {$errstr}  in {$errfile} on line {$errline}";

    throw new Exception($errorMessage);
}


/**
 * A simple Zend_Mail wraper
 * @param string $email_to
 * @param string $email_to_name
 * @param string $email_from
 * @param string $email_from_name
 * @param string $email_subject
 * @param string text|HTML $email_body
 * @param bool $is_hmtl
 * @return bool 
 */
function send_ZendMail($email_to, $email_to_name, $email_from, $email_from_name, $email_subject, $email_body, $is_hmtl = false)
{
    $mail = new Zend_Mail('UTF-8');
    if ($is_hmtl) {
        $mail->setBodyHtml($email_body);
    } else {
        $mail->setBodyText($email_body);
    }

    $mail->setFrom($email_from, $email_from_name)
        ->addTo($email_to, $email_to_name)
        ->setSubject($email_subject);

    $mail->send();

    return true;
}

/**
 * @return string IP address from the request
 */
function getIPAddr() {
    if ($_SERVER["REMOTE_ADDR"] == "184.73.218.108") {
        return getIPfromXForwarded();
    }
    return $_SERVER["REMOTE_ADDR"];
}

/**
 * Get the ip address if the request is forwared 
 * @return string IP address 
 */
function getIPfromXForwarded() {
    $ipString= getenv("HTTP_X_FORWARDED_FOR");
    $addr = explode(",",$ipString);
    return $addr[sizeof($addr)-1];
}
?>
