<?php

use zavoloklom\ispp\sync\src\Synchronization;
use zavoloklom\ispp\sync\src\SlackNotification;
use zavoloklom\ispp\sync\src\Education;

define('CONFIG', include_once(__DIR__ . '\config.php'));

// Setup application (как в файле app.php)
$sync = new Synchronization();
$sync->department_id = array_key_exists('department', CONFIG) ? CONFIG['department']['id'] : 0;
// Setup education schedule
$sync->education = array_key_exists('education', CONFIG) ? new Education(CONFIG['education']) : NULL;
// Setup notification system
if (array_key_exists('slack', CONFIG) && is_array(CONFIG['slack']) && $notification = new SlackNotification(CONFIG['slack'], CONFIG)) {
  $sync->notification = $notification;
  $sync->notificationEnabled = true;
}
\Codeception\Util\Fixtures::add('sync', $sync);

// Применить ecafe_dump
if (CONFIG['local_server']['adapter'] == 'mysql') {
  $dumpConfig = CONFIG['local_server']['options'];
  $password = $dumpConfig['password'] ? '-p '.$dumpConfig['password'] : '';
  exec('mysql -h '.$dumpConfig['host'].' -u '.$dumpConfig['username'].' '.$password.' < '.__DIR__.'/_data/ecafe_dump.sql');
}
