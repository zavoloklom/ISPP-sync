<?php
use Codeception\Util\Fixtures;

$config = include_once(__DIR__ . '\config.php');
Fixtures::add('config', $config);
Fixtures::add('sync_class', new \zavoloklom\ispp\sync\src\Synchronization($config));