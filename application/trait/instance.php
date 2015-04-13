<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-05
 */

namespace TRA;

trait instance
{
    static private $singletons = [];

    /*
    // reserve for php 5.6
    static function instance(...$args)
    {
        return new self(...$args);
    }
    */

    public static function singleton()
    {
        $args = func_get_args();
        $key = md5(json_encode($args));
        if (isset (self::$singletons[$key])) {
            $ist = self::$singletons[$key];
        } else {
            $ist = call_user_func_array('self::instance', $args);
            self::$singletons[$key] = $ist;
        }
        return $ist;
    }
}