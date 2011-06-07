<?php
/**
 * Main config data for the application
 * 
 * @package Core
 * @category Config
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
$connectDb['host'] = 'localhost';
$connectDb['port'] = 3306;
$connectDb['user'] = 'root';
$connectDb['pass'] = 'nigrum';
$connectDb['datb'] = 'stages';
$connectDb['charSet'] = 'utf8';

define('SERVER_URI', 'http://' . $_SERVER["HTTP_HOST"].'/');
define('SECURE_SERVER_URI', 'https://' . $_SERVER["HTTP_HOST"].'/');
define('APP_VERSION', '201104111955');
?>
