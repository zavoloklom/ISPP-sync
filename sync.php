#!/usr/bin/env php
<?php
/**
 * Console bootstrap file.
 */

set_time_limit(0);
//error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set("Europe/Moscow");

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require(__DIR__ . '/vendor/autoload.php');

// Load configuration
$config = include_once('config.php');

// ASCII Logotype
$helper = new \zavoloklom\ispp\sync\src\Helper();
$helper->printLogo();

// Approved commands and options
$actions = [
  'groups',
  'students',
  'events',
  'all'
];
$options = [];

// Проверка корректности действий и установка действия по умолчанию
if ($argc >= 2 && in_array($argv[1], $actions)) {
  $action = $argv[1];
  // Make connection
  $connection = new \zavoloklom\ispp\sync\src\Synchronization($config);
  $connection->$action();
} else {
  $helper->printHelp();
}
