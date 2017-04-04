<?php
define('CONFIG', include_once(__DIR__ . '\config.php'));

// Применить ecafe_dump
if (CONFIG['local_server']['adapter'] == 'mysql') {
  $dumpConfig = CONFIG['local_server']['options'];
  $password = $dumpConfig['password'] ? '-p '.$dumpConfig['password'] : '';
  exec('mysql -h '.$dumpConfig['host'].' -u '.$dumpConfig['username'].' '.$password.' < '.__DIR__.'/_data/ecafe_dump.sql');
}
