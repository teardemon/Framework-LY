<?php
$CONF_system=array (
  'path' => 
  array (
    'tmp' => '/tmp',
    'shm' => '/dev/shm',
    'log' => '/var/logs',
  ),
  'namescope' => 
  array (
    'Core' => '/core',
    'CTL' => '/application/control',
    'MDE' => '/application/model',
    'LIB' => '/application/library',
    'OBJ' => '/application/object',
    'TRA' => '/application/trait',
  ),
  'file' => 
  array (
    'mysql-error' => '/var/logs/mysql-error.log',
  ),
  'setting' => 
  array (
    'env' => 'DEV',
    'resource_path' => '/resource/',
    'cookie_prefix' => '_',
    'session_prefix' => '_',
    'debug_mode' => '1',
    'version' => '0',
  ),
);
return $CONF_system;
