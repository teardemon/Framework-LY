<?php
$CONF_email=array (
  'noreply' => 
  array (
    'Host' => 'smtp.exmail.qq.com',
    'Port' => '25',
    'Username' => 'noreply@gamebegin.com',
    'Password' => 'xiaobao134',
    'From' => 'noreply@gamebegin.com',
    'FromName' => 'noreply',
    'WordWrap' => '50',
    'SMTPAuth' => '1',
    'SMTPSecure' => 'ssl',
    'SMTPDebug' => '1',
    'CharSet' => 'UTF-8',
    'isSMTP' => 
    array (
      0 => '',
    ),
    'isHTML' => 
    array (
      0 => '1',
    ),
  ),
  'huangl' => 
  array (
    'Host' => 'smtp.exmail.qq.com',
    'Port' => '465',
    'Username' => 'huangl@gamebegin.com',
    'Password' => 'gb1CvWSzTk',
    'From' => 'huangl@gamebegin.com',
    'FromName' => 'huangl',
    'WordWrap' => '50',
    'SMTPAuth' => '1',
    'SMTPSecure' => 'ssl',
    'SMTPDebug' => '1',
    'CharSet' => 'UTF-8',
    'isSMTP' => 
    array (
      0 => '',
    ),
    'isHTML' => 
    array (
      0 => '1',
    ),
  ),
);
return $CONF_email;
