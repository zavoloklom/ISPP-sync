<?php

return (array) array(
  'organization' => 'Test Organization',
  'department' => [
    'name'    => 'Department Name',
    'address' => 'Department Address'
  ],
  'local_server' => [
    'adapter' => 'mysql',
    'options' => [
      'driver'    => 'mysql',
      'host'      => '127.0.0.1',
      'username'  => 'root',
      'password'  => '',
      'database'  => 'ispp-ecafe-test',
    ],
  ],
  'web_server' => [
    'adapter' => 'mysql',
    'options' => [
      'driver'    => 'mysql',
      'host'      => '127.0.0.1',
      'username'  => 'root',
      'password'  => '',
      'database'  => 'ispp-iseduc-test',
    ],
  ],
  'slack' => false
);
