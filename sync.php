#!/usr/bin/env php
<?php
/**
 * Console bootstrap file.
 */

use zavoloklom\ispp\sync\src\Helper;
use zavoloklom\ispp\sync\src\Synchronization;

set_time_limit(0);
//error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set("Europe/Moscow");

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require_once(__DIR__ . '/vendor/autoload.php');

// Load configuration
$config = include_once('config.php');

// ASCII Logotype
Helper::printLogo();

// Проверка корректности действий и установка действия по умолчанию
if ($argc >= 2 && in_array($argv[1], Synchronization::actionsArray())) {
  $action = $argv[1];
  $app = new Synchronization($config);
  //$app->setupConnection();
  //$app->execute($action, $params)
  $app->$action();
} else {
  Helper::printHelp();
}
