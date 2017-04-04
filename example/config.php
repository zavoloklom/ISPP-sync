<?php

return (array) array(
  'organization' => [
    'name'    => '',
    'website' => [
      'name'  => '',
      'url'   => '',
      'ip'    => ''
    ]
  ],
  'department' => [
    'id'      => '',
    'name'    => '',
    'address' => ''
  ],
  'education' => [
    'year_start'  => '',
    'year_finish' => '',
    'isSaturdayAreHoliday' => false,
    'isSundayAreHoliday' => true,
    'holidays' => [],
    'vacationIntervals' => []
  ],
  'local_server' => [
    'adapter' => '',
    'options' => [
      'driver'    => '',
      'host'      => '',
      'username'  => '',
      'password'  => '',
      'database'  => '',
    ],
  ],
  'web_server' => [
    'adapter' => '',
    'options' => [
      'driver'    => '',
      'host'      => '',
      'username'  => '',
      'password'  => '',
      'database'  => '',
    ],
  ],
  'slack' => [
    'webhook'   => '',
    'options'   => []
  ]
);
