<?php

return (array) array(
  'organization' => [
    'name'    => 'Test Organization',
    'website' => [
      'name'  => 'Site Name',
      'url'   => 'http://127.0.0.1/',
      'ip'    => '127.0.0.1'
    ]
  ],
  'department' => [
    'id'      => 0,
    'name'    => 'Department Name',
    'address' => 'Department Address',
  ],
  'education' => [
    'year_start'  => '2016-09-01',
    'year_finish' => '2017-05-01',
    'isSaturdayAreHoliday' => false,
    'isSundayAreHoliday' => true,
    'holidays' => [
      '15.10.2016',
      '22.10.2016',
      '26.11.2016',
      '23.02.2017',
      '24.02.2017',
      '25.02.2017',
      '8.03.2017',
      '14.04.2017',
      '1.05.2017',
      '8.05.2017',
      '9.05.2017'
    ],
    'vacationIntervals' => [
      ['31.10.2016', '6.11.2016'],
      ['26.12.2016', '8.01.2017'],
      ['27.03.2017', '2.04.2017'],
      ['01.06.2017', '31.08.2017']
    ]
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
