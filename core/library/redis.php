<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-10-21
 */

namespace Core\library;


class redis extends \Redis
{
    private static $instances = [];

    public static function instance($name)
    {
        if (isset(self::$instances[$name]) && self::$instances[$name] instanceof \Redis) {
            $inst = self::$instances[$name];
        } else {
            $config = \config::load('redis', $name);
            $host = $config['host'];
            $port = isset($config['port']) ? $config['port'] : 6379;
            $timeout = isset($config['timeout']) ? $config['timeout'] : 0.0;
            $options = isset($config['options']) ? $config['options'] : [];
            $inst = new self;
            $inst->connect($host, $port, $timeout);
            foreach ($options as $key => $val) {
                $key = @constant("\\Redis::$key") ? : $key;
                $val = @constant("\\Redis::$val") ? : $val;
                $inst->setOption($key, $val);
            }
        }
        return $inst;
    }

}
