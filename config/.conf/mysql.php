<?php
$CONF_mysql=array (
  'test' => 
  array (
    'dsn' => 'mysql:host=192.168.56.10;dbname=test',
    'username' => 'test',
    'password' => 'mysqltest',
    'options' => 
    array (
      'ATTR_DEFAULT_FETCH_MODE' => 'FETCH_ASSOC',
      'MYSQL_ATTR_INIT_COMMAND' => 'set names utf8',
    ),
  ),
  'main' => 
  array (
    'dsn' => 'mysql:host=localhost',
    'username' => 'root',
    'password' => 'ELING-Mysql',
    'options' => 
    array (
      'MYSQL_ATTR_INIT_COMMAND' => 'set names utf8',
      'ATTR_DEFAULT_FETCH_MODE' => 'FETCH_ASSOC',
    ),
  ),
);
return $CONF_mysql;
