<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-10-21
 */

namespace Core\library;

require_once __DIR__ . '/phpmailer/PHPMailerAutoload.php';

class email
{
    private static $instances = [];

    public static function instance($name)
    {
        if (isset (self::$instances[$name]) ) {
            $inst = self::$instances[$name];
        } else {
            $config = \config::load('email', $name);
            $inst = new \PHPMailer;
            foreach ($config as $key => $val) {
                if (is_array($val)) {
                    call_user_func_array([$inst, $key], $val);
                } else {
                    $inst->$key = $val;
                }
            }
        }
        return $inst;
    }

} 