#!/usr/bin/env php
<?php
/**
 * Console bootstrap file.
 */

use zavoloklom\ispp\sync\src\Helper;
use zavoloklom\ispp\sync\src\Synchronization;
use zavoloklom\ispp\sync\src\SlackNotification;
use zavoloklom\ispp\sync\src\Education;

set_time_limit(0);
//error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set("Europe/Moscow");

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require_once(__DIR__ . '/vendor/autoload.php');

// Load configuration
define('CONFIG', include_once('config.php'));

// ASCII Logotype
Helper::printLogo();

// Setup application
$sync = new Synchronization();
$sync->department_id = array_key_exists('department', CONFIG) ? CONFIG['department']['id'] : 0;

// Setup education schedule
$sync->education = array_key_exists('education', CONFIG) ? new Education(CONFIG['education']) : NULL;

// Setup notification system
if (array_key_exists('slack', CONFIG) && is_array(CONFIG['slack']) && $notification = new SlackNotification(CONFIG['slack'], CONFIG)) {
    $sync->notification = $notification;
    $sync->notificationEnabled = true;
}

// Проверка корректности действий и установка действия по умолчанию
if ($argc >= 2 && in_array($argv[1], Synchronization::actionsArray())) {
    $action = $argv[1];

    // execute action
    $sync->testConnections(CONFIG['local_server'], CONFIG['web_server']);
    $sync->$action();
} else {
    Helper::printHelp();
}
