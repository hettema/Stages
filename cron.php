<?php
/**
 * Cron job file called from the crontab
 * 
 * @package Cron
 * @category PHP
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
include_once("app/main.php");

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

try {
    App_Main::runCrontab();
} catch (Exception $e) {
    App_Main::printException($e);
}
